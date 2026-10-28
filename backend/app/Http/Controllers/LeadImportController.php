<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Property;
use App\Models\User;
use App\Support\LeadSpreadsheetReader;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

/**
 * POST /api/leads/import: Bulk import buyer leads from CSV or Excel (.csv, .xlsx, .xls, .tsv)
 * including Meta Lead Ads exports and custom CRM spreadsheets.
 */
class LeadImportController extends Controller
{
    /** Known column aliases mapped to canonical lead fields. */
    private const COLUMN_ALIASES = [
        'name' => ['name', 'full_name', 'fullname', 'buyer_name', 'buyer', 'client_name', 'client', 'customer_name', 'customer', 'contact_name', 'নাম', 'ক্রেতার নাম', 'গ্রাহকের নাম'],
        'first_name' => ['first_name', 'firstname', 'given_name'],
        'last_name' => ['last_name', 'lastname', 'surname', 'family_name'],
        'phone' => ['phone', 'phone_number', 'phonenumber', 'mobile', 'mobile_number', 'mobilenumber', 'contact', 'contact_number', 'contact_no', 'cell', 'cell_phone', 'telephone', 'tel', 'whatsapp', 'whatsapp_number', 'ফোন', 'মোবাইল', 'ফোন নম্বর', 'মোবাইল নম্বর', 'নাম্বার'],
        'email' => ['email', 'email_address', 'emailaddress', 'e_mail', 'mail', 'ইমেইল'],
        'property_title' => ['property', 'property_title', 'property_name', 'project', 'project_name', 'project_title', 'listing', 'asset', 'interest', 'প্রজেক্ট', 'প্রপার্টি'],
        'status' => ['status', 'stage', 'pipeline_stage', 'crm_stage', 'lead_stage', 'স্ট্যাটাস', 'পর্যায়'],
        'budget_range' => ['budget', 'budget_range', 'price_range', 'expected_budget', 'বাজেট'],
        'buyer_category' => ['buyer_category', 'category', 'buyer_type', 'client_type'],
        'investment_readiness' => ['investment_readiness', 'readiness', 'buying_timeline', 'timeline'],
        'preferred_contact' => ['preferred_contact', 'contact_preference', 'contact_method', 'channel'],
        'callback_time' => ['callback_time', 'best_time_to_call', 'best_time', 'call_time'],
        'notes' => ['notes', 'note', 'message', 'remarks', 'remark', 'comment', 'comments', 'description', 'summary', 'মন্তব্য', 'নোট'],
        'meta_lead_id' => ['id', 'lead_id', 'meta_lead_id', 'leadgen_id'],
        'created_time' => ['created_time', 'created_at', 'date', 'inquiry_date', 'submitted_at', 'timestamp'],
        'ad_id' => ['ad_id'],
        'ad_name' => ['ad_name'],
        'adset_id' => ['adset_id'],
        'adset_name' => ['adset_name'],
        'campaign_id' => ['campaign_id'],
        'campaign_name' => ['campaign_name', 'campaign'],
        'form_id' => ['form_id'],
        'form_name' => ['form_name'],
        'platform' => ['platform', 'source', 'utm_source'],
        'is_organic' => ['is_organic'],
        'lead_status' => ['lead_status'],
    ];

    public function import(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['nullable', 'file', 'max:20480'],
            'rows' => ['nullable', 'array', 'max:5000'],
            'default_property' => ['nullable', 'string', 'max:255'],
            'property_id' => ['nullable', 'integer'],
            'assigned_to' => ['nullable', 'integer'],
            'duplicate_mode' => ['nullable', Rule::in(['skip', 'update', 'allow'])],
        ]);

        if (! $request->hasFile('file') && empty($data['rows'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a CSV or Excel (.xlsx) file to import.',
                'errors' => ['file' => ['Please select a CSV or Excel (.xlsx) file to import.']],
            ], 422);
        }

        $user = $request->user();
        if ($user && ! $user->isLeadAdmin()) {
            $owner = $user;
        } else {
            $owner = ! empty($data['assigned_to']) ? User::find($data['assigned_to']) : null;
            if ($owner && ! $owner->isStaff()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leads can only be assigned to team members.',
                    'errors' => ['assigned_to' => ['Leads can only be assigned to team members.']],
                ], 422);
            }
        }

        $property = ! empty($data['property_id']) ? Property::find($data['property_id']) : null;
        $defaultPropertyTitle = trim((string) ($property?->title ?? ($data['default_property'] ?? '')));
        $duplicateMode = $data['duplicate_mode'] ?? 'skip';
        $filename = $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : 'manual_rows.csv';

        try {
            $rows = $request->hasFile('file')
                ? LeadSpreadsheetReader::readUploadedFile($request->file('file'))
                : array_values(array_filter((array) $data['rows'], 'is_array'));
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => ['file' => [$e->getMessage()]],
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not parse the uploaded file. Please upload a valid .csv or .xlsx file.',
                'errors' => ['file' => ['Could not parse the uploaded file.']],
            ], 422);
        }

        if ($rows === []) {
            return response()->json([
                'success' => false,
                'message' => 'No lead rows were found in the uploaded file.',
                'errors' => ['file' => ['The file is empty or contains only headers.']],
            ], 422);
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $rowErrors = [];
        $importedLeads = [];

        foreach ($rows as $index => $rawRow) {
            $rowNumber = $index + 2; // 1-based + header row
            $mapped = $this->mapRow($rawRow);

            $rawPhone = $mapped['phone'] ?? '';
            $phone = PhoneNumber::normalize($rawPhone);

            // Validate phone: must have at least 7 digits
            $digitsOnly = preg_replace('/\D+/', '', $phone);
            if ($phone === '' || strlen($digitsOnly) < 7) {
                $failedCount++;
                if (count($rowErrors) < 50) {
                    $rowErrors[] = [
                        'row' => $rowNumber,
                        'name' => $mapped['name'] ?: 'Unknown',
                        'phone' => $rawPhone,
                        'reason' => $rawPhone === '' ? 'Missing phone number' : "Invalid phone number ({$rawPhone})",
                    ];
                }
                continue;
            }

            $metaLeadId = $this->cleanMetaId($mapped['meta_lead_id'] ?? '', 'l:');
            $hasMetaColumns = ! empty($mapped['form_id']) || ! empty($mapped['ad_id']) || ! empty($mapped['campaign_id']) || str_starts_with(trim((string) ($mapped['meta_lead_id'] ?? '')), 'l:');
            if (! $hasMetaColumns) {
                // Avoid treating an ordinary sequential spreadsheet "id" column (1, 2, 3) as a Meta Lead ID
                $metaLeadId = null;
            }

            $existing = null;
            if ($metaLeadId) {
                $existing = Lead::withTrashed()->where('meta_lead_id', $metaLeadId)->first();
            }
            if (! $existing) {
                $existing = Lead::withTrashed()->where('phone', $phone)->latest('id')->first();
            }

            if ($existing && $duplicateMode === 'skip') {
                $skippedCount++;
                continue;
            }

            $name = $mapped['name'] !== '' ? $mapped['name'] : 'Imported Buyer';
            $email = $mapped['email'] !== '' && filter_var($mapped['email'], FILTER_VALIDATE_EMAIL) ? $mapped['email'] : null;
            $formId = $this->cleanMetaId($mapped['form_id'] ?? '', 'f:');
            $adId = $this->cleanMetaId($mapped['ad_id'] ?? '', 'a:');
            $campaignId = $this->cleanMetaId($mapped['campaign_id'] ?? '', 'c:');
            $formName = $mapped['form_name'] !== '' ? $mapped['form_name'] : null;
            $campaignName = $mapped['campaign_name'] !== '' ? $mapped['campaign_name'] : null;

            $propertyTitle = $mapped['property_title']
                ?: $defaultPropertyTitle
                ?: $formName
                ?: $campaignName
                ?: 'General Inquiry';

            $stage = $this->resolveStage($mapped['status'] ?? '');
            $platformRaw = strtolower(trim($mapped['platform'] ?? ''));
            $isInstagram = in_array($platformRaw, ['ig', 'instagram'], true);
            $isFacebook = in_array($platformRaw, ['fb', 'facebook', 'meta'], true) || $hasMetaColumns;

            $leadType = $isInstagram
                ? 'Instagram Lead Ad'
                : ($isFacebook ? 'Facebook Lead Ad' : 'CSV/Excel Import');

            $utmSource = $isInstagram
                ? 'instagram'
                : ($isFacebook ? 'facebook' : ($platformRaw !== '' ? $platformRaw : 'csv_import'));

            $isOrganic = filter_var($mapped['is_organic'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $utmMedium = $hasMetaColumns ? ($isOrganic ? 'organic' : 'paid_social') : 'spreadsheet';

            $answers = $mapped['answers'];
            $messageLines = [];
            if ($mapped['notes'] !== '') {
                $messageLines[] = $mapped['notes'];
            }
            foreach ($answers as $ans) {
                $messageLines[] = "{$ans['question']}: {$ans['answer']}";
            }
            $message = $messageLines !== [] ? implode("\n", $messageLines) : null;

            $createdAt = $this->parseDate($mapped['created_time'] ?? '');

            if ($existing && $duplicateMode === 'update') {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                if ($name !== 'Imported Buyer') {
                    $existing->name = $name;
                }
                if ($email) {
                    $existing->email = $email;
                }
                if ($mapped['property_title'] !== '' || $defaultPropertyTitle !== '') {
                    $existing->property_title = $propertyTitle;
                }
                if ($property) {
                    $existing->property_id = $property->id;
                }
                if ($mapped['budget_range'] !== '') {
                    $existing->budget_range = $mapped['budget_range'];
                }
                if ($mapped['buyer_category'] !== '') {
                    $existing->buyer_category = $mapped['buyer_category'];
                }
                if ($mapped['investment_readiness'] !== '') {
                    $existing->investment_readiness = $mapped['investment_readiness'];
                }
                if ($mapped['preferred_contact'] !== '') {
                    $existing->preferred_contact = $mapped['preferred_contact'];
                }
                if ($mapped['callback_time'] !== '') {
                    $existing->callback_time = $mapped['callback_time'];
                }
                if ($mapped['status'] !== '') {
                    $existing->status = $stage;
                }
                if ($owner) {
                    $existing->assigned_to = $owner->id;
                }
                if ($answers !== []) {
                    $existingAnswers = is_array($existing->meta_answers) ? $existing->meta_answers : [];
                    $byQuestion = [];
                    foreach ($existingAnswers as $item) {
                        if (isset($item['question'], $item['answer'])) {
                            $byQuestion[$item['question']] = $item['answer'];
                        }
                    }
                    foreach ($answers as $item) {
                        $byQuestion[$item['question']] = $item['answer'];
                    }
                    $mergedAnswers = [];
                    foreach ($byQuestion as $q => $a) {
                        $mergedAnswers[] = ['question' => (string) $q, 'answer' => (string) $a];
                    }
                    $existing->meta_answers = $mergedAnswers;
                }
                if ($message) {
                    $existing->message = $existing->message
                        ? (str_contains($existing->message, $message) ? $existing->message : $existing->message."\n".$message)
                        : $message;
                }

                $existing->save();
                $this->log($user, $existing, 'manual', "Updated from spreadsheet import ({$filename})", [
                    'channel' => 'CSV/Excel Import',
                    'source' => 'spreadsheet_import',
                    'filename' => $filename,
                ]);

                $updatedCount++;
                if (count($importedLeads) < 50) {
                    $fresh = $existing->fresh();
                    LeadCrmController::attachActivitySummary($fresh);
                    $importedLeads[] = $fresh;
                }
                continue;
            }

            // Create new lead
            $leadMetaId = $metaLeadId;
            if ($leadMetaId && Lead::withTrashed()->where('meta_lead_id', $leadMetaId)->exists()) {
                $leadMetaId = null;
            }

            $lead = new Lead([
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'property_id' => $property?->id,
                'property_title' => $propertyTitle,
                'lead_type' => $leadType,
                'buyer_category' => $mapped['buyer_category'] ?: null,
                'investment_readiness' => $mapped['investment_readiness'] ?: null,
                'budget_range' => $mapped['budget_range'] ?: null,
                'preferred_contact' => $mapped['preferred_contact'] ?: 'Phone Call',
                'callback_time' => $mapped['callback_time'] ?: null,
                'message' => $message,
                'status' => $stage,
                'utm_source' => $utmSource,
                'utm_medium' => $utmMedium,
                'utm_campaign' => $campaignName,
                'utm_term' => $mapped['adset_name'] ?: null,
                'utm_content' => $mapped['ad_name'] ?: null,
                'form_version' => 'csv_import',
                'assigned_to' => $owner?->id,
                'contact_consented_at' => $createdAt ?? now(),
                'meta_lead_id' => $leadMetaId,
                'meta_form_id' => $formId,
                'meta_form_name' => $formName,
                'meta_ad_id' => $adId,
                'meta_campaign_id' => $campaignId,
                'meta_platform' => $isInstagram ? 'instagram' : ($isFacebook ? 'facebook' : null),
                'meta_answers' => $answers !== [] ? $answers : null,
            ]);

            if ($createdAt) {
                $lead->created_at = $createdAt;
            }
            $lead->save();

            $this->log($user, $lead, 'manual', $mapped['notes'] ?: "Imported from {$filename}", [
                'channel' => 'CSV/Excel Import',
                'source' => 'spreadsheet_import',
                'filename' => $filename,
            ]);

            if ($owner) {
                $this->log($user, $lead, 'assign', "Assigned to {$owner->name}", ['user_id' => $owner->id]);
            }

            $createdCount++;
            if (count($importedLeads) < 50) {
                $fresh = $lead->fresh();
                LeadCrmController::attachActivitySummary($fresh);
                $importedLeads[] = $fresh;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Import complete: {$createdCount} created, {$updatedCount} updated, {$skippedCount} skipped.",
            'data' => [
                'total_rows' => count($rows),
                'created' => $createdCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
                'failed' => $failedCount,
                'errors' => $rowErrors,
                'leads' => $importedLeads,
            ],
        ]);
    }

    /**
     * Map an arbitrary associative row from CSV/Excel into canonical fields + extra question/answer pairs.
     *
     * @param  array<string, mixed>  $rawRow
     * @return array<string, mixed>
     */
    private function mapRow(array $rawRow): array
    {
        $mapped = [
            'name' => '',
            'first_name' => '',
            'last_name' => '',
            'phone' => '',
            'email' => '',
            'property_title' => '',
            'status' => '',
            'budget_range' => '',
            'buyer_category' => '',
            'investment_readiness' => '',
            'preferred_contact' => '',
            'callback_time' => '',
            'notes' => '',
            'meta_lead_id' => '',
            'created_time' => '',
            'ad_id' => '',
            'ad_name' => '',
            'adset_id' => '',
            'adset_name' => '',
            'campaign_id' => '',
            'campaign_name' => '',
            'form_id' => '',
            'form_name' => '',
            'platform' => '',
            'is_organic' => '',
            'lead_status' => '',
            'answers' => [],
        ];

        $lookup = [];
        foreach (self::COLUMN_ALIASES as $targetField => $aliases) {
            foreach ($aliases as $alias) {
                $lookup[$this->normalizeHeader($alias)] = $targetField;
            }
        }

        foreach ($rawRow as $header => $value) {
            $headerStr = trim((string) $header);
            $valStr = trim((string) $value);
            if ($headerStr === '' || $valStr === '') {
                continue;
            }

            $normalized = $this->normalizeHeader($headerStr);
            $target = $lookup[$normalized] ?? null;

            if ($target !== null) {
                if ($mapped[$target] === '') {
                    $mapped[$target] = $valStr;
                }
                continue;
            }

            // Skip internal spreadsheet serial number columns like "sl", "serial", "row"
            if (in_array($normalized, ['sl', 'sl_no', 'serial', 'serial_no', 'row', 'no'], true)) {
                continue;
            }

            $mapped['answers'][] = [
                'question' => $this->readableText($headerStr),
                'answer' => $this->readableText($valStr),
            ];
        }

        if ($mapped['name'] === '' && ($mapped['first_name'] !== '' || $mapped['last_name'] !== '')) {
            $mapped['name'] = trim($mapped['first_name'].' '.$mapped['last_name']);
        }

        return $mapped;
    }

    private function normalizeHeader(string $header): string
    {
        $clean = strtolower(trim($header));
        $clean = preg_replace('/[\s\-\.\/]+/u', '_', $clean);
        $clean = preg_replace('/[^\p{L}\p{N}_]+/u', '', $clean);

        return trim((string) $clean, '_');
    }

    private function readableText(string $value): string
    {
        return trim(str_replace('_', ' ', $value));
    }

    private function cleanMetaId(string $value, string $prefix): ?string
    {
        $val = trim($value);
        if (str_starts_with(strtolower($val), strtolower($prefix))) {
            $val = trim(substr($val, strlen($prefix)));
        }

        return $val !== '' ? $val : null;
    }

    private function resolveStage(string $rawStage): string
    {
        $clean = strtolower(trim($rawStage));
        if ($clean === '') {
            return 'New';
        }
        foreach (LeadCrmController::STAGES as $stage) {
            if (strtolower($stage) === $clean) {
                return $stage;
            }
        }

        return 'New';
    }

    private function parseDate(string $rawDate): ?Carbon
    {
        $val = trim($rawDate);
        if ($val === '') {
            return null;
        }
        try {
            return Carbon::parse($val)->utc();
        } catch (Throwable) {
            return null;
        }
    }

    /** @param  array<string, mixed>  $meta */
    private function log(?User $user, Lead $lead, string $type, ?string $body, array $meta = []): void
    {
        if ($user) {
            $meta['by_user_id'] = $user->id;
            $meta['by_user_name'] = $user->name;
            $meta['by_user_role'] = $user->role;
        }

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user?->id,
            'type' => $type,
            'body' => $body,
            'meta' => $meta ?: null,
            'occurred_at' => now(),
        ]);
    }
}

