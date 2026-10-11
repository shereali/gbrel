<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookLeadRequest;
use App\Jobs\SendMetaEvent;
use App\Models\BookOrder;
use App\Models\Lead;
use App\Support\EasyPay;
use App\Support\VisitorJourney;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Sells the e-book on /book through EasyPay.
 *
 * 1. POST /api/book-orders saves the buyer as a CRM lead plus a pending order, and returns EasyPay's checkout link.
 * 2. The buyer pays (bKash/Nagad/Rocket) and EasyPay sends them back to /book-thanks?order=<public id>.
 * 3. That page polls GET /api/book-orders/{public id}; while pending, the order is checked with EasyPay's
 *    verify-payment API. EasyPay's webhook (POST /api/book-orders/webhook) triggers the same check.
 * 4. Once EasyPay says "completed" for the full price, the order is paid and the answer carries the download link.
 */
class BookOrderController extends Controller
{
    /** Re-asking EasyPay more often than this for the same order is pointless while the buyer waits. */
    private const CHECK_EVERY_SECONDS = 4;

    /** POST /api/book-orders */
    public function store(StoreBookLeadRequest $request): JsonResponse
    {
        if (! EasyPay::configured()) {
            Log::error('Book order refused: EASYPAY_API_KEY is not set.');

            return response()->json(['success' => false, 'message' => 'Online payment is not switched on yet.'], 503);
        }

        $input = $request->validated();
        $requestId = $input['request_id'] ?? null;

        // A double tap or a retry after a slow network reuses the same checkout.
        if ($requestId && ($existing = BookOrder::where('request_id', $requestId)->first())) {
            if ($existing->isPaid() || ($existing->status === BookOrder::PENDING && $existing->checkout_url)) {
                return $this->checkoutResponse($existing, 200);
            }
        }

        $price = self::price();
        $reason = $input['reason'] ?? null;
        $fields = [
            'property_id' => null,
            'property_title' => BookDownloadController::BOOK_TITLE,
            'name' => $input['name'],
            'phone' => $input['phone'],
            'email' => $input['email'] ?? null,
            'lead_type' => 'Book Order',
            'buyer_category' => $reason ? BookDownloadController::REASONS[$reason] : null,
            'preferred_contact' => $input['preferred_contact'] ?? 'WhatsApp',
            'message' => $input['message'] ?? null,
            'status' => 'New',
            'utm_source' => $input['utm_source'] ?? null,
            'utm_medium' => $input['utm_medium'] ?? null,
            'utm_campaign' => $input['utm_campaign'] ?? null,
            'utm_content' => $input['utm_content'] ?? null,
            'utm_term' => $input['utm_term'] ?? null,
            'next_step' => 'Book payment pending ('.$price.' BDT): help them finish checkout',
            'contact_consented_at' => ! empty($input['contact_consent']) ? now() : null,
        ];
        $lead = $requestId ? Lead::firstOrCreate(['request_id' => $requestId], $fields) : Lead::create($fields);
        if ($lead->wasRecentlyCreated) {
            VisitorJourney::record($request, $lead, $input);
        }

        $order = $requestId
            ? BookOrder::firstOrNew(['request_id' => $requestId])
            : new BookOrder;
        $order->fill([
            'public_id' => $order->public_id ?: Str::random(32),
            'lead_id' => $lead->id,
            'name' => $input['name'],
            'phone' => $input['phone'],
            'email' => $input['email'] ?? null,
            'amount' => $price,
            'currency' => 'BDT',
            'status' => BookOrder::PENDING,
            'gateway' => 'easypay',
            'tracking' => array_filter([
                'vid' => $input['vid'] ?? $request->cookie('gb_vid'),
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                'fbc' => $request->cookie('_fbc'),
                'fbp' => $request->cookie('_fbp'),
                'source_url' => $request->headers->get('referer'),
            ]),
        ])->save();

        try {
            $payment = EasyPay::createPayment([
                'amount' => $price,
                'customer_name' => $order->name,
                'customer_phone' => self::localPhone($order->phone),
                'customer_email' => $order->email,
                'callback_url' => self::siteUrl().'/book-thanks?order='.$order->public_id,
                'webhook_url' => self::siteUrl().'/api/book-orders/webhook',
            ]);
        } catch (\Throwable $e) {
            Log::error('EasyPay create-payment failed for book order '.$order->id, ['error' => $e->getMessage()]);
            $order->update(['status' => BookOrder::FAILED]);

            return response()->json(['success' => false, 'message' => 'Payment page could not be opened. Please try again.'], 502);
        }

        $order->update([
            'gateway_txn_id' => $payment['transaction_id'],
            'checkout_url' => $payment['checkout_url'],
            'fee' => is_numeric($payment['raw']['fee'] ?? null) ? $payment['raw']['fee'] : null,
            'gateway_response' => ['create' => $payment['raw']],
        ]);

        return $this->checkoutResponse($order, 201);
    }

    /** GET /api/book-orders/{publicId} — the thank-you page asks this until the payment is confirmed. */
    public function show(string $publicId): JsonResponse
    {
        $order = BookOrder::where('public_id', $publicId)->firstOrFail();

        if ($order->status === BookOrder::PENDING && $order->gateway_txn_id
            && (! $order->last_checked_at || $order->last_checked_at->diffInSeconds(now()) >= self::CHECK_EVERY_SECONDS)) {
            $this->refresh($order, 8);
        }

        return response()->json(['success' => true, 'data' => $this->publicData($order)]);
    }

    /**
     * POST /api/book-orders/webhook — EasyPay's instant notification. It is not signed, so it only tells us
     * which order to re-check; the order becomes paid only on EasyPay's own verify-payment answer.
     * EasyPay wants a 200 within 5 seconds, so the check is short and any failure still answers 200.
     */
    public function webhook(Request $request): JsonResponse
    {
        $transactionId = (string) $request->input('transaction_id', '');
        $order = $transactionId !== '' ? BookOrder::where('gateway_txn_id', $transactionId)->first() : null;

        if ($order && $order->status === BookOrder::PENDING) {
            $this->refresh($order, 3);
        }

        return response()->json(['success' => true, 'received' => true]);
    }

    private function refresh(BookOrder $order, int $timeout): void
    {
        $order->forceFill(['last_checked_at' => now()])->save();

        try {
            $result = EasyPay::verify((string) $order->gateway_txn_id, $timeout);
        } catch (\Throwable $e) {
            Log::warning('EasyPay verify-payment failed for book order '.$order->id, ['error' => $e->getMessage()]);

            return;
        }

        if (! $result['completed']) {
            return;
        }

        $this->markPaid($order, $result);
    }

    /**
     * @param  array{completed: bool, amount: float|null, trx_id: string|null, payment_method: string|null, sender_number: string|null, payment_status: string|null, raw: array<string, mixed>}  $result
     */
    private function markPaid(BookOrder $order, array $result): void
    {
        $becamePaid = DB::transaction(function () use ($order, $result) {
            $locked = BookOrder::whereKey($order->id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== BookOrder::PENDING) {
                return false;
            }

            $paid = $result['amount'] ?? (float) $locked->amount;
            $fullyPaid = $paid + 0.009 >= (float) $locked->amount;
            $locked->forceFill([
                'status' => $fullyPaid ? BookOrder::PAID : BookOrder::UNDERPAID,
                'paid_amount' => $paid,
                'trx_id' => $result['trx_id'],
                'payment_method' => $result['payment_method'],
                'sender_number' => $result['sender_number'],
                'paid_at' => now(),
                'gateway_response' => [...($locked->gateway_response ?? []), 'verify' => $result['raw']],
            ])->save();

            if ($lead = Lead::find($locked->lead_id)) {
                $lead->forceFill([
                    'lead_type' => $fullyPaid ? 'Book Purchase' : 'Book Order',
                    'next_step' => $fullyPaid
                        ? 'Bought the book: call to offer land/flat help'
                        : 'Paid '.$paid.' BDT of '.$locked->amount.': check with the buyer',
                ])->save();
            }
            $order->setRawAttributes($locked->getAttributes(), true);

            return $fullyPaid;
        });

        if ($becamePaid) {
            $this->sendPurchaseToMeta($order);
        }
    }

    /** Same event id as the browser Purchase event on /book-thanks, so Meta counts the sale once. */
    private function sendPurchaseToMeta(BookOrder $order): void
    {
        $tracking = $order->tracking ?? [];
        SendMetaEvent::dispatchAfterResponse('Purchase', 'book-'.$order->public_id, [
            'phone' => $order->phone,
            'first_name' => explode(' ', trim($order->name))[0] ?: null,
            'visitor_id' => $tracking['vid'] ?? null,
            'ip' => $tracking['ip'] ?? null,
            'user_agent' => $tracking['user_agent'] ?? null,
            'fbc' => $tracking['fbc'] ?? null,
            'fbp' => $tracking['fbp'] ?? null,
        ], [
            'value' => (float) $order->amount,
            'currency' => 'BDT',
            'content_ids' => ['book-hidden-business-of-land'],
            'content_type' => 'product',
            'content_name' => 'The Hidden Business of Land',
        ], $tracking['source_url'] ?? self::siteUrl().'/book');
    }

    private function checkoutResponse(BookOrder $order, int $status): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [...$this->publicData($order), 'checkout_url' => $order->isPaid() ? null : $order->checkout_url],
        ], $status);
    }

    /** @return array<string, mixed> */
    private function publicData(BookOrder $order): array
    {
        return [
            'order' => $order->public_id,
            'status' => $order->status,
            'name' => $order->name,
            'amount' => (float) $order->amount,
            'currency' => $order->currency,
            'trx_id' => $order->isPaid() ? $order->trx_id : null,
            'download_url' => $order->isPaid() ? BookDownloadController::downloadUrl($order) : null,
        ];
    }

    public static function price(): int
    {
        return (int) config('services.book.price', 500);
    }

    private static function siteUrl(): string
    {
        return rtrim((string) (config('services.easypay.site_url') ?: config('app.url')), '/');
    }

    /** EasyPay asks for local numbers (017XXXXXXXX); numbers from abroad are sent as they are. */
    private static function localPhone(string $phone): string
    {
        return str_starts_with($phone, '+880') ? '0'.substr($phone, 4) : $phone;
    }
}
