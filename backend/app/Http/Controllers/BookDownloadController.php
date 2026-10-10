<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookLeadRequest;
use App\Models\Lead;
use App\Support\VisitorJourney;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Free e-book "The Hidden Business of Land" (/book landing page).
 * A visitor gives a name and number, the lead is saved like any other website lead (and sent to Meta through
 * VisitorJourney, with the request id as the event id), and the answer comes back with a personal download link.
 * The PDF lives in resources/books, outside the public folder, so it is only reachable through that link.
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

    /** POST /api/book-leads */
    public function store(StoreBookLeadRequest $request): JsonResponse
    {
        $input = $request->validated();
        $reason = $input['reason'];

        $fields = [
            'property_id' => null,
            'property_title' => self::BOOK_TITLE,
            'name' => $input['name'],
            'phone' => $input['phone'],
            'email' => $input['email'] ?? null,
            'lead_type' => 'Book Download',
            'buyer_category' => self::REASONS[$reason],
            'preferred_contact' => $input['preferred_contact'] ?? 'WhatsApp',
            'message' => $input['message'] ?? null,
            'status' => 'New',
            'utm_source' => $input['utm_source'] ?? null,
            'utm_medium' => $input['utm_medium'] ?? null,
            'utm_campaign' => $input['utm_campaign'] ?? null,
            'utm_content' => $input['utm_content'] ?? null,
            'utm_term' => $input['utm_term'] ?? null,
            'next_step' => $reason === 'learn' ? 'Send book follow-up' : 'Call: '.self::REASONS[$reason],
            'contact_consented_at' => ! empty($input['contact_consent']) ? now() : null,
        ];

        $lead = ! empty($input['request_id'])
            ? Lead::firstOrCreate(['request_id' => $input['request_id']], $fields)
            : Lead::create($fields);
        if ($lead->wasRecentlyCreated) {
            VisitorJourney::record($request, $lead, $input);
        }

        return response()->json([
            'success' => true,
            'data' => ['id' => $lead->id, 'download_url' => $this->downloadUrl($lead)],
        ], $lead->wasRecentlyCreated ? 201 : 200);
    }

    /** GET /api/book/download?lead=…&expires=…&sig=…  (add &save=1 to save the file instead of opening it) */
    public function download(Request $request): BinaryFileResponse|JsonResponse
    {
        $leadId = (int) $request->query('lead');
        $expires = (int) $request->query('expires');
        $signature = (string) $request->query('sig');

        if ($leadId < 1 || $expires < time() || ! hash_equals($this->signature($leadId, $expires), $signature)) {
            return response()->json(['success' => false, 'message' => 'This download link has expired. Get a new one at gbrel.com/book.'], 403);
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

    private function downloadUrl(Lead $lead): string
    {
        $expires = now()->addDays(self::LINK_DAYS)->getTimestamp();

        // Relative to the site, so it works behind the proxy and on localhost alike.
        return '/api/book/download?'.http_build_query([
            'lead' => $lead->id,
            'expires' => $expires,
            'sig' => $this->signature($lead->id, $expires),
        ]);
    }

    private function signature(int $leadId, int $expires): string
    {
        return hash_hmac('sha256', 'book|'.$leadId.'|'.$expires, (string) config('app.key'));
    }
}
