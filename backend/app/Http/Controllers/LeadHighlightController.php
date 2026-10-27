<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Property;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Which answers the CRM shows up front for each lead form ("highlighted fields").
 * Every Meta lead form has its own questions; the website inquiry form is the "website" form.
 * Lives under /api/leads/*, so ProtectLeadData allows leads.view to read and leads.manage to change it.
 */
class LeadHighlightController extends Controller
{
    public const SETTING_KEY = 'lead_highlight_fields';

    public const WEBSITE_FORM = 'website';

    /** Fields of the website inquiry form, keyed by the lead column the CRM reads them from. */
    public const WEBSITE_FIELDS = [
        'callback_time' => 'Best time to call',
        'investment_readiness' => 'Wants to buy',
        'budget_range' => 'Budget',
        'buyer_category' => 'Plans to use it for',
        'preferred_contact' => 'Prefers',
        'email' => 'Email',
    ];

    /** Shown highlighted until someone chooses otherwise: when to call the buyer. */
    private const DEFAULT_PATTERN = '/(কখন.*কল|কল.*কখন|call)/iu';

    /** GET /api/leads/highlights */
    public function index(): JsonResponse
    {
        $saved = (array) Setting::getVal(self::SETTING_KEY, []);
        $questionsByForm = [];
        $names = [];
        $metaCounts = [];

        Lead::query()
            ->whereNotNull('meta_form_id')
            ->orderByDesc('id')
            ->limit(2000)
            ->get(['meta_form_id', 'meta_form_name', 'property_title', 'campaign_name', 'meta_answers'])
            ->each(function (Lead $lead) use (&$questionsByForm, &$names, &$metaCounts) {
                $formId = (string) $lead->meta_form_id;
                $metaCounts[$formId] = ($metaCounts[$formId] ?? 0) + 1;

                if (empty($names[$formId]) || str_starts_with($names[$formId], 'Form ')) {
                    $title = $lead->meta_form_name ?: ($lead->property_title ?: ($lead->campaign_name ?: null));
                    if ($title && ! in_array($title, ['Facebook Lead Ad', 'Instagram Lead Ad', 'General Inquiry'], true)) {
                        $names[$formId] = $title;
                    } else {
                        $names[$formId] ??= 'Form '.$formId;
                    }
                }

                foreach ((array) $lead->meta_answers as $answer) {
                    if (! empty($answer['question'])) {
                        $q = trim((string) $answer['question']);
                        if ($q !== '') {
                            $questionsByForm[$formId][$q] = true;
                        }
                    }
                }
            });

        // Meta forms come FIRST (on top), sorted by lead count descending
        uksort($names, fn ($a, $b) => ($metaCounts[$b] ?? 0) <=> ($metaCounts[$a] ?? 0));

        $metaForms = [];
        foreach ($names as $formId => $name) {
            $metaForms[] = [
                'key' => (string) $formId,
                'name' => $name,
                'fields' => array_keys($questionsByForm[$formId] ?? []),
                'count' => $metaCounts[$formId] ?? 0,
                'source' => 'meta',
            ];
        }

        // Distinct website projects/properties from leads and published properties
        $websiteProjects = Lead::query()
            ->whereNull('meta_form_id')
            ->whereNotNull('property_title')
            ->where('property_title', '!=', '')
            ->whereNotIn('property_title', ['General Inquiry', 'Direct Inquiry'])
            ->distinct()
            ->orderBy('property_title')
            ->pluck('property_title');

        try {
            $publishedProps = Property::query()
                ->whereNotIn('status', Property::HIDDEN_STATUSES)
                ->whereNotNull('title')
                ->where('title', '!=', '')
                ->orderBy('title')
                ->pluck('title');
            $websiteProjects = $websiteProjects->merge($publishedProps)->unique()->values();
        } catch (\Throwable $e) {
            // Ignore if properties table isn't accessible
        }

        $webForms = [[
            'key' => self::WEBSITE_FORM,
            'name' => 'Website inquiry form',
            'fields' => array_values(self::WEBSITE_FIELDS),
            'source' => 'website',
        ]];

        foreach ($websiteProjects as $projectTitle) {
            $webForms[] = [
                'key' => 'website:'.$projectTitle,
                'name' => 'Website - '.$projectTitle,
                'fields' => array_values(self::WEBSITE_FIELDS),
                'source' => 'website',
                'project' => $projectTitle,
            ];
        }

        // Meta forms on top, Website projects below (exact same order as All Projects dropdown)
        $forms = array_merge($metaForms, $webForms);

        $selected = [];
        foreach ($forms as $form) {
            if (array_key_exists($form['key'], $saved)) {
                $selected[$form['key']] = array_values(array_intersect((array) $saved[$form['key']], $form['fields']));
            } elseif (str_starts_with($form['key'], 'website:') && array_key_exists(self::WEBSITE_FORM, $saved)) {
                $selected[$form['key']] = array_values(array_intersect((array) $saved[self::WEBSITE_FORM], $form['fields']));
            } else {
                $selected[$form['key']] = array_values(array_filter($form['fields'], fn (string $field) => preg_match(self::DEFAULT_PATTERN, $field)));
            }
        }

        return response()->json(['success' => true, 'data' => ['forms' => $forms, 'selected' => $selected]]);
    }

    /** PUT /api/leads/highlights: the fields to highlight for one form. */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'form' => ['required', 'string', 'max:255'],
            'fields' => ['present', 'array', 'max:20'],
            'fields.*' => ['string', 'max:300'],
        ]);

        $saved = (array) Setting::getVal(self::SETTING_KEY, []);
        $saved[$data['form']] = array_values(array_unique($data['fields']));
        Setting::setVal(self::SETTING_KEY, $saved);

        return response()->json(['success' => true, 'data' => ['form' => $data['form'], 'fields' => $saved[$data['form']]]]);
    }
}
