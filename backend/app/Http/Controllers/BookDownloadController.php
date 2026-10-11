<?php

namespace App\Http\Controllers;

use App\Models\BookOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Delivers the paid e-book "The Hidden Business of Land" (/book sales page).
 * The PDF lives in resources/books, outside the public folder, so it is only reachable through a signed link
 * that belongs to a paid order (see BookOrderController). The buyer's thank-you page hands out a fresh link
 * whenever it is opened, so the order link itself is the buyer's permanent access.
 */
class BookDownloadController extends Controller
{
    public const BOOK_TITLE = 'Book: The Hidden Business of Land';

    private const FILE = 'books/the-hidden-business-of-land.pdf';

    private const DOWNLOAD_NAME = 'The-Hidden-Business-of-Land.pdf';

    /** A link keeps working for a week, so a reader can open it again from another phone. */
    private const LINK_DAYS = 7;

    /** Answers to "why do you want this book?", as the CRM stores them. */
    public const REASONS = [
        'buy' => 'Wants to buy land/flat',
        'sell' => 'Wants to sell land/flat',
        'business' => 'Wants to start a land business',
        'invest_abroad' => 'Wants to invest from abroad (NRB)',
        'learn' => 'Just learning',
    ];

    /** POST /api/book-leads — the free giveaway has ended; old cached pages get a clear answer. */
    public function retired(): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'The free download has ended. Please buy the book at gbrel.com/book.'], 410);
    }

    /** GET /api/book/download?order=…&expires=…&sig=…  (add &save=1 to save the file instead of opening it) */
    public function download(Request $request): BinaryFileResponse|JsonResponse
    {
        $orderId = (int) $request->query('order');
        $expires = (int) $request->query('expires');
        $signature = (string) $request->query('sig');

        if ($orderId < 1 || $expires < time() || ! hash_equals(self::signature($orderId, $expires), $signature)) {
            return response()->json(['success' => false, 'message' => 'This download link has expired. Open your order link again to get a new one.'], 403);
        }
        if (! BookOrder::whereKey($orderId)->where('status', BookOrder::PAID)->exists()) {
            return response()->json(['success' => false, 'message' => 'This order is not paid.'], 403);
        }

        $path = resource_path(self::FILE);
        if (! is_file($path)) {
            return response()->json(['success' => false, 'message' => 'The book file is missing on the server.'], 404);
        }

        $disposition = $request->boolean('save') ? 'attachment' : 'inline';

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.self::DOWNLOAD_NAME.'"',
            'Cache-Control' => 'private, max-age=3600',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    public static function downloadUrl(BookOrder $order): string
    {
        $expires = now()->addDays(self::LINK_DAYS)->getTimestamp();

        // Relative to the site, so it works behind the proxy and on localhost alike.
        return '/api/book/download?'.http_build_query([
            'order' => $order->id,
            'expires' => $expires,
            'sig' => self::signature($order->id, $expires),
        ]);
    }

    private static function signature(int $orderId, int $expires): string
    {
        return hash_hmac('sha256', 'book-order|'.$orderId.'|'.$expires, (string) config('app.key'));
    }
}
