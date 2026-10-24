<?php

namespace App\Http\Controllers;

use App\Jobs\ImportMetaLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Meta Lead Ads webhook: every Facebook / Instagram instant-form submission on the subscribed Page lands in the CRM.
 * Set the callback URL in the Meta App to https://<api host>/api/meta/leadgen and subscribe the Page to "leadgen".
 */
class MetaLeadWebhookController extends Controller
{
    /** GET /api/meta/leadgen: Meta's one-time handshake when the callback URL is saved. */
    public function verify(Request $request): Response
    {
        $expected = (string) config('services.meta.webhook_verify_token');
        $given = (string) $request->query('hub_verify_token', '');

        if ($request->query('hub_mode') !== 'subscribe' || $expected === '' || ! hash_equals($expected, $given)) {
            return response('Forbidden', 403);
        }

        return response((string) $request->query('hub_challenge', ''), 200)->header('Content-Type', 'text/plain');
    }

    /** POST /api/meta/leadgen: answers 200 straight away; each lead is fetched after the response is sent. */
    public function receive(Request $request): JsonResponse
    {
        if (! $this->hasValidSignature($request)) {
            Log::warning('Meta leadgen webhook rejected: bad or missing signature', ['ip' => $request->ip()]);

            return response()->json(['success' => false, 'message' => 'Invalid signature.'], 403);
        }

        $queued = 0;
        foreach ((array) $request->input('entry', []) as $entry) {
            foreach ((array) ($entry['changes'] ?? []) as $change) {
                $leadgenId = (string) ($change['value']['leadgen_id'] ?? '');
                if (($change['field'] ?? null) === 'leadgen' && preg_match('/^\d{5,30}$/', $leadgenId)) {
                    ImportMetaLead::dispatchAfterResponse($leadgenId);
                    $queued++;
                }
            }
        }

        return response()->json(['success' => true, 'queued' => $queued]);
    }

    /** Meta signs the raw body with the App Secret (X-Hub-Signature-256: sha256=<hex>). */
    private function hasValidSignature(Request $request): bool
    {
        $secret = (string) config('services.meta.app_secret');
        $header = (string) $request->header('X-Hub-Signature-256', '');
        if ($secret === '' || ! str_starts_with($header, 'sha256=')) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $request->getContent(), $secret), substr($header, 7));
    }
}
