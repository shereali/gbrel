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
        $forms = [[
            'key' => self::WEBSITE_FORM,
            'name' => 'Website inquiry form',
            'fields' => array_values(self::WEBSITE_FIELDS),
        ]];

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

        foreach ($websiteProjects as $projectTitle) {
            $forms[] = [
                'key' => 'website:'.$projectTitle,
                'name' => 'Website - '.$projectTitle,
                'fields' => array_values(self::WEBSITE_FIELDS),
            ];
        }

        $questionsByForm = [];
        $names = [];
        Lead::query()->whereNotNull('meta_form_id')->orderByDesc('id')->limit(2000)
            ->get(['meta_form_id', 'meta_form_name', 'meta_answers'])
            ->each(function (Lead $lead) use (&$questionsByForm, &$names) {
                $names[$lead->meta_form_id] ??= $lead->meta_form_name ?: 'Form '.$lead->meta_form_id;
                foreach ((array) $lead->meta_answers as $answer) {
                    if (! empty($answer['question'])) {
                        $questionsByForm[$lead->meta_form_id][$answer['question']] = true;
                    }
                }
            });
        foreach ($names as $formId => $name) {
            $forms[] = ['key' => (string) $formId, 'name' => $name, 'fields' => array_keys($questionsByForm[$formId] ?? [])];
        }

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
