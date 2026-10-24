<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Pulls a lead from a Meta (Facebook / Instagram) Lead Ad instant form through the Graph API and saves it in the CRM.
 * Used by the leadgen webhook (one lead at a time) and by `php artisan meta:import-leads` (a whole form, for backfills).
 * Every lead is keyed on its Meta lead id, so running either path twice never creates a duplicate.
 */
class MetaLeadImporter
{
    private const LEAD_FIELDS = 'id,created_time,field_data,ad_id,ad_name,adset_id,adset_name,campaign_id,campaign_name,form_id,platform,is_organic';

    /** Form fields that become the lead's own columns instead of a question/answer pair. */
    private const CONTACT_FIELDS = ['full_name', 'first_name', 'last_name', 'name', 'phone_number', 'phone', 'email'];

    public static function isConfigured(): bool
    {
        return (bool) config('services.meta.page_access_token');
    }

    /** Fetches one lead by its leadgen id and saves it; returns the stored lead (new or already imported). */
    public static function importById(string $leadgenId): Lead
    {
        $existing = Lead::withTrashed()->where('meta_lead_id', $leadgenId)->first();
        if ($existing) {
            return $existing;
        }

        return self::store(self::graph($leadgenId, ['fields' => self::LEAD_FIELDS]));
    }

    /**
     * Imports new leads from every lead form of the Page the access token belongs to.
     * New forms are picked up automatically, so a new ad needs no setup. Archived forms are only read on a full backfill.
     *
     * @return array{forms: int, created: int, skipped: int}
     */
    public static function syncPageForms(?Carbon $since = null): array
    {
        $totals = ['forms' => 0, 'created' => 0, 'skipped' => 0];
        $page = self::graph('me/leadgen_forms', ['fields' => 'id,name,status', 'limit' => 100]);
        while (true) {
            foreach ($page['data'] ?? [] as $form) {
                if ($since && strtoupper((string) ($form['status'] ?? 'ACTIVE')) !== 'ACTIVE') {
                    continue;
                }
                if (! empty($form['name'])) {
                    Cache::put("meta_lead_form_name_{$form['id']}", $form['name'], now()->addDay());
                }
                $counts = self::importForm((string) $form['id'], $since);
                $totals['forms']++;
                $totals['created'] += $counts['created'];
                $totals['skipped'] += $counts['skipped'];
            }
            $next = $page['paging']['next'] ?? null;
            if (! $next) {
                break;
            }
            $page = self::request($next, []);
        }

        return $totals;
    }

    /**
     * Imports every lead of a form, newest first, optionally only those created after $since.
     *
     * @return array{created: int, skipped: int}
     */
    public static function importForm(string $formId, ?Carbon $since = null): array
    {
        $query = ['fields' => self::LEAD_FIELDS, 'limit' => 100];
        if ($since) {
            $query['filtering'] = json_encode([['field' => 'time_created', 'operator' => 'GREATER_THAN', 'value' => $since->timestamp]]);
        }

        $counts = ['created' => 0, 'skipped' => 0];
        $page = self::graph("{$formId}/leads", $query);
        while (true) {
            foreach ($page['data'] ?? [] as $raw) {
                $lead = self::store($raw);
                $lead->wasRecentlyCreated ? $counts['created']++ : $counts['skipped']++;
            }
            $next = $page['paging']['next'] ?? null;
            if (! $next) {
                break;
            }
            $page = self::request($next, []);
        }

        return $counts;
    }

    /**
     * Saves one Graph API lead object.
     *
     * @param  array<string, mixed>  $raw
     */
    public static function store(array $raw): Lead
    {
        $metaLeadId = (string) ($raw['id'] ?? '');
        if ($metaLeadId === '') {
            throw new RuntimeException('Meta returned a lead without an id.');
        }
        $existing = Lead::withTrashed()->where('meta_lead_id', $metaLeadId)->first();
        if ($existing) {
            return $existing;
        }

        $fields = [];
        foreach ($raw['field_data'] ?? [] as $field) {
            $fields[(string) ($field['name'] ?? '')] = trim(implode(', ', (array) ($field['values'] ?? [])));
        }
        $answers = [];
        foreach ($fields as $question => $answer) {
            if ($question !== '' && $answer !== '' && ! in_array($question, self::CONTACT_FIELDS, true)) {
                $answers[] = ['question' => self::readable($question), 'answer' => self::readable($answer)];
            }
        }

        $formId = (string) ($raw['form_id'] ?? '');
        $formName = $formId !== '' ? self::formName($formId) : null;
        $platform = strtolower((string) ($raw['platform'] ?? 'fb'));
        $isInstagram = in_array($platform, ['ig', 'instagram'], true);
        $isOrganic = filter_var($raw['is_organic'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $createdAt = ! empty($raw['created_time']) ? Carbon::parse($raw['created_time'])->utc() : now();
        $name = $fields['full_name'] ?? $fields['name'] ?? trim(($fields['first_name'] ?? '').' '.($fields['last_name'] ?? ''));

        $attributes = [
            'name' => $name !== '' ? $name : 'Facebook lead',
            'phone' => PhoneNumber::normalize($fields['phone_number'] ?? $fields['phone'] ?? ''),
            'email' => ($fields['email'] ?? '') !== '' ? $fields['email'] : null,
            'property_title' => $formName ?: ($raw['campaign_name'] ?? null) ?: 'Facebook Lead Ad',
            'lead_type' => $isInstagram ? 'Instagram Lead Ad' : 'Facebook Lead Ad',
            'preferred_contact' => 'Phone Call',
            'message' => $answers ? implode("\n", array_map(fn (array $a) => "{$a['question']}: {$a['answer']}", $answers)) : null,
            'status' => 'New',
            'utm_source' => $isInstagram ? 'instagram' : 'facebook',
            'utm_medium' => $isOrganic ? 'organic' : 'paid_social',
            'utm_campaign' => $raw['campaign_name'] ?? null,
            'utm_term' => $raw['adset_name'] ?? null,
            'utm_content' => $raw['ad_name'] ?? null,
            'contact_consented_at' => $createdAt,
            'meta_lead_id' => $metaLeadId,
            'meta_form_id' => $formId !== '' ? $formId : null,
            'meta_form_name' => $formName,
            'meta_ad_id' => $raw['ad_id'] ?? null,
            'meta_campaign_id' => $raw['campaign_id'] ?? null,
            'meta_platform' => $isInstagram ? 'instagram' : 'facebook',
            'meta_answers' => $answers ?: null,
        ];

        try {
            $lead = new Lead($attributes);
            $lead->created_at = $createdAt;
            $lead->save();
        } catch (UniqueConstraintViolationException) {
            // Meta retried the webhook while the first delivery was still being saved.
            return Lead::withTrashed()->where('meta_lead_id', $metaLeadId)->firstOrFail();
        }

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => null,
            'type' => 'meta_lead',
            'body' => 'Form: '.($formName ?: $formId).($raw['ad_name'] ?? null ? "\nAd: {$raw['ad_name']}" : ''),
            'meta' => array_filter(['form_id' => $formId, 'ad_id' => $raw['ad_id'] ?? null, 'campaign_id' => $raw['campaign_id'] ?? null, 'platform' => $attributes['meta_platform']]),
            'occurred_at' => $createdAt,
        ]);

        return $lead;
    }

    /** Form names rarely change, so they are cached for a day instead of being fetched for every lead. */
    public static function formName(string $formId): ?string
    {
        return Cache::remember("meta_lead_form_name_{$formId}", now()->addDay(), function () use ($formId) {
            try {
                return self::graph($formId, ['fields' => 'name'])['name'] ?? null;
            } catch (\Throwable $e) {
                Log::warning('Could not read the Meta lead form name', ['form_id' => $formId, 'error' => $e->getMessage()]);

                return null;
            }
        });
    }

    /** Meta sends answers like "এই_সপ্তাহের_মধ্যেই_কিনতে_পারব"; staff read them better with spaces. */
    private static function readable(string $value): string
    {
        return trim(str_replace('_', ' ', $value));
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private static function graph(string $path, array $query): array
    {
        $version = config('services.meta.graph_version', 'v21.0');

        return self::request("https://graph.facebook.com/{$version}/{$path}", $query + ['access_token' => config('services.meta.page_access_token')]);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private static function request(string $url, array $query): array
    {
        $response = Http::timeout(15)->retry(2, 500, throw: false)->get($url, $query);
        if ($response->failed()) {
            throw new RuntimeException('Meta Graph API error '.$response->status().': '.($response->json('error.message') ?? $response->body()));
        }

        return $response->json() ?? [];
    }
}
