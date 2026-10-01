<?php

namespace Tests\Feature;

use App\Jobs\SendMetaEvent;
use App\Models\Lead;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class LeadAnswersTest extends TestCase
{
    use RefreshDatabase;

    private const VID = 'vid0123456789abcdef0123456789ab';

    /** The first screen of the survey saves the lead after only two answers and the contact details. */
    private function earlyLead(array $override = []): Lead
    {
        $property = Property::create(['title' => 'Test land share', 'address' => 'Test address', 'area_name' => 'Test area', 'price' => 4200000]);
        $payload = array_merge([
            'name' => 'Test Buyer', 'phone' => '01712345678', 'property_id' => $property->id,
            'property_title' => 'Test land share', 'buyer_category' => 'Investment', 'investment_readiness' => 'Within 30 days',
            'preferred_contact' => 'WhatsApp', 'form_version' => 'property_inquiry_v1', 'contact_consent' => true,
            'request_id' => (string) Str::uuid(), 'vid' => self::VID,
            'message' => "Lead score: WARM (4/9)\nSurvey: partial (2 of 5 questions)\nContact consent: granted\nCTA: hero_button",
        ], $override);
        $this->postJson('/api/leads', $payload)->assertCreated();

        return Lead::sole();
    }

    public function test_later_answers_are_added_to_the_same_lead_and_replace_the_lines_they_change(): void
    {
        Bus::fake();
        $lead = $this->earlyLead();

        $this->postJson('/api/lead-answers', [
            'request_id' => $lead->request_id, 'budget_range' => 'Listed price fits budget', 'payment' => 'Own funds, lump sum',
            'lead_score' => 'HOT (8/9)', 'answered' => 4, 'total' => 5, 'next_step' => 'HOT lead — site visit',
        ])->assertOk();

        $lead->refresh();
        $this->assertSame('Listed price fits budget', $lead->budget_range);
        $this->assertSame('HOT lead — site visit', $lead->next_step);
        $lines = explode("\n", $lead->message);
        $this->assertContains('Lead score: HOT (8/9)', $lines);
        $this->assertContains('Payment plan: Own funds, lump sum', $lines);
        $this->assertContains('Survey: partial (4 of 5 questions)', $lines);
        $this->assertContains('CTA: hero_button', $lines, 'lines the survey did not touch stay as they were');
        $this->assertCount(1, array_filter($lines, fn ($l) => str_starts_with($l, 'Lead score: ')));
        $this->assertDatabaseCount('leads', 1);
    }

    public function test_finishing_the_survey_is_marked_complete(): void
    {
        $lead = $this->earlyLead();

        $this->postJson('/api/lead-answers', ['request_id' => $lead->request_id, 'residence' => 'Abroad (NRB)', 'lead_score' => 'WARM (5/9)', 'answered' => 5, 'total' => 5])->assertOk();

        $this->assertStringContainsString('Survey: complete (5 of 5 questions)', $lead->fresh()->message);
        $this->assertStringContainsString('Lives: Abroad (NRB)', $lead->fresh()->message);
    }

    public function test_hot_leads_from_tracked_visitors_send_one_qualified_event(): void
    {
        $lead = $this->earlyLead();
        Bus::fake();

        $body = ['request_id' => $lead->request_id, 'lead_score' => 'HOT (8/9)'];
        $this->postJson('/api/lead-answers', $body)->assertOk();
        $this->postJson('/api/lead-answers', $body)->assertOk();

        Bus::assertDispatchedAfterResponseTimes(SendMetaEvent::class, 1);
        Bus::assertDispatchedAfterResponse(SendMetaEvent::class, fn (SendMetaEvent $e) => $e->eventName === 'QualifiedLead' && $e->eventId === $lead->request_id.'-q');
    }

    public function test_unknown_old_or_malformed_requests_change_nothing(): void
    {
        $lead = $this->earlyLead();

        $this->postJson('/api/lead-answers', ['request_id' => (string) Str::uuid(), 'budget_range' => 'x'])->assertNotFound();
        $this->postJson('/api/lead-answers', ['request_id' => 'not-a-uuid'])->assertUnprocessable();
        $this->postJson('/api/lead-answers', ['request_id' => $lead->request_id, 'lead_score' => 'SUPER HOT (99/9)'])->assertUnprocessable();

        $lead->forceFill(['created_at' => now()->subHours(7)])->save();
        $this->postJson('/api/lead-answers', ['request_id' => $lead->request_id, 'budget_range' => 'Too late'])->assertNotFound();
        $this->assertNull($lead->fresh()->budget_range);
    }

    public function test_the_lead_list_and_admin_stay_protected_while_the_answers_route_is_public(): void
    {
        $lead = $this->earlyLead();

        $this->getJson('/api/leads')->assertUnauthorized();
        $this->postJson('/api/lead-answers', ['request_id' => $lead->request_id, 'budget_range' => 'Needs full cost breakdown'])->assertOk();
    }
}
