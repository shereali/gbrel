<?php

namespace App\Jobs;

use App\Support\SiteSettings;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Server-side copy of a browser pixel event (Meta Conversions API), so ad blockers cannot hide leads.
 * eventId must equal the browser pixel's eventID or Meta counts the event twice.
 *
 * There is no queue worker on the server, so callers use dispatchAfterResponse(): it runs right after the
 * visitor's response has been sent. Nothing here may ever fail a lead, so errors are only logged.
 */
class SendMetaEvent
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $userData  raw values (phone, first_name, visitor_id, ip, user_agent, fbc, fbp); hashed in handle()
     * @param  array<string, mixed>  $customData
     */
    public function __construct(
        public string $eventName,
        public string $eventId,
        public array $userData,
        public array $customData = [],
        public ?string $sourceUrl = null,
        public ?int $eventTime = null,
    ) {}

    public function handle(): void
    {
        $token = config('services.meta.capi_token');
        $pixelId = trim((string) (SiteSettings::all()['meta_pixel_id'] ?: config('services.meta.pixel_id')));
        if (! $token || ! preg_match('/^\d{10,20}$/', $pixelId)) {
            return;
        }

        $hash = fn ($value) => $value ? [hash('sha256', mb_strtolower(trim((string) $value)))] : null;
        $user = array_filter([
            'ph' => $hash(preg_replace('/\D/', '', (string) ($this->userData['phone'] ?? ''))),
            'fn' => $hash($this->userData['first_name'] ?? null),
            'external_id' => $hash($this->userData['visitor_id'] ?? null),
            'client_ip_address' => $this->userData['ip'] ?? null,
            'client_user_agent' => $this->userData['user_agent'] ?? null,
            'fbc' => $this->userData['fbc'] ?? null,
            'fbp' => $this->userData['fbp'] ?? null,
        ]);

        $payload = [
            'access_token' => $token,
            'data' => [[
                'event_name' => $this->eventName,
                'event_time' => $this->eventTime ?? time(),
                'event_id' => $this->eventId,
                'action_source' => 'website',
                'event_source_url' => $this->sourceUrl,
                'user_data' => $user,
                'custom_data' => $this->customData,
            ]],
        ];
        if (config('services.meta.test_event_code')) {
            $payload['test_event_code'] = config('services.meta.test_event_code');
        }

        try {
            $response = Http::timeout(10)->retry(2, 500, throw: false)->post("https://graph.facebook.com/v21.0/{$pixelId}/events", $payload);
            if ($response->failed()) {
                Log::warning('Meta Conversions API rejected an event', ['event' => $this->eventName, 'status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Throwable $e) {
            Log::warning('Meta Conversions API request failed', ['event' => $this->eventName, 'error' => $e->getMessage()]);
        }
    }
}
