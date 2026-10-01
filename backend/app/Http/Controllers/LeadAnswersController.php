<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Support\VisitorJourney;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The buyer survey saves the lead as soon as name and number are given (two quick questions first), then asks the
 * money questions. This stores those later answers on the same lead, one at a time, so a buyer who leaves halfway
 * still keeps the answers they gave. The lead is found by the request id the browser made for it.
 */
class LeadAnswersController extends Controller
{
    /** Buyers can add answers for a while after sending the form, not forever. */
    private const WINDOW_HOURS = 6;

    /** POST /api/lead-answers */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'request_id' => ['required', 'uuid'],
            'budget_range' => ['nullable', 'string', 'max:255'],
            'payment' => ['nullable', 'string', 'max:255'],
            'residence' => ['nullable', 'string', 'max:255'],
            'lead_score' => ['nullable', 'regex:/^(HOT|WARM|COLD) \(\d{1,2}\/\d{1,2}\)$/'],
            'answered' => ['nullable', 'integer', 'min:0', 'max:20'],
            'total' => ['nullable', 'integer', 'min:1', 'max:20'],
            'next_step' => ['nullable', 'string', 'max:255'],
        ]);

        $lead = Lead::where('request_id', $data['request_id'])->where('created_at', '>=', now()->subHours(self::WINDOW_HOURS))->first();
        if (! $lead) {
            return response()->json(['success' => false, 'message' => 'This request can no longer be updated.'], 404);
        }

        $tierBefore = VisitorJourney::tier($lead);
        $lines = array_filter([
            'Lead score' => $data['lead_score'] ?? null,
            'Payment plan' => $data['payment'] ?? null,
            'Lives' => $data['residence'] ?? null,
            'Survey' => isset($data['answered'], $data['total'])
                ? ($data['answered'] >= $data['total'] ? 'complete' : 'partial').' ('.$data['answered'].' of '.$data['total'].' questions)'
                : null,
        ]);

        $lead->forceFill(array_filter([
            'budget_range' => $data['budget_range'] ?? null,
            'next_step' => $data['next_step'] ?? null,
            'message' => $this->withLines((string) $lead->message, $lines),
        ], fn ($value) => $value !== null))->save();

        // Only now can a lead turn out to be hot, so this is when Meta hears about it.
        if (VisitorJourney::tier($lead) === 'HOT' && $tierBefore !== 'HOT') {
            VisitorJourney::recordQualified($request, $lead);
        }

        return response()->json(['success' => true, 'data' => ['id' => $lead->id]]);
    }

    /**
     * The survey stores its answers as "Key: value" lines in the lead's message. Replaces a line that is already
     * there and adds the ones that are not, leaving every other line as it was.
     *
     * @param  array<string, string>  $lines
     */
    private function withLines(string $message, array $lines): string
    {
        $existing = $message === '' ? [] : explode("\n", $message);
        foreach ($lines as $key => $value) {
            $line = $key.': '.$value;
            $index = collect($existing)->search(fn (string $current) => str_starts_with($current, $key.': '));
            if ($index === false) {
                $existing[] = $line;
            } else {
                $existing[$index] = $line;
            }
        }

        return implode("\n", $existing);
    }
}
