<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use App\Models\Viewing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class LeadCrmTest extends TestCase
{
    use RefreshDatabase;

    private function lead(array $attributes = []): Lead
    {
        return Lead::create(array_merge(['name' => 'Test Buyer', 'phone' => '+8801712345678', 'property_title' => 'Lake view plot', 'status' => 'New', 'preferred_contact' => 'WhatsApp'], $attributes));
    }

    private function tomorrow(): string
    {
        return now('Asia/Dhaka')->addDay()->toDateString();
    }

    public function test_only_staff_with_lead_access_can_use_the_crm(): void
    {
        $lead = $this->lead();

        $this->getJson("/api/leads/{$lead->id}/crm")->assertUnauthorized();
        $this->signInAs('buyer');
        $this->getJson("/api/leads/{$lead->id}/crm")->assertForbidden();
        $this->postJson("/api/leads/{$lead->id}/activities", ['type' => 'note', 'body' => 'x'])->assertForbidden();
        $this->postJson("/api/leads/{$lead->id}/visits", ['date' => $this->tomorrow(), 'time' => '10:00', 'visit_type' => 'Site visit'])->assertForbidden();
        $this->assertDatabaseCount('lead_activities', 0);
    }

    public function test_logging_a_call_marks_the_lead_contacted_and_clears_a_due_follow_up(): void
    {
        $admin = $this->signInAs('admin');
        $lead = $this->lead(['next_follow_up_at' => now()->subHour()]);

        $this->postJson("/api/leads/{$lead->id}/activities", ['type' => 'call', 'outcome' => 'Answered', 'body' => 'Wants a site visit on Friday.'])->assertOk();

        $lead->refresh();
        $this->assertSame('Contacted', $lead->status);
        $this->assertNotNull($lead->last_contacted_at);
        $this->assertNull($lead->next_follow_up_at);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'call', 'user_id' => $admin->id]);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'stage']);

        $timeline = $this->getJson("/api/leads/{$lead->id}/crm")->assertOk()->json('data.activities');
        $this->assertSame('call', collect($timeline)->firstWhere('type', 'call')['type']);
        $this->assertSame('Answered', collect($timeline)->firstWhere('type', 'call')['meta']['outcome']);
        $this->assertSame($admin->name, $timeline[0]['user']['name']);
    }

    public function test_a_call_needs_an_outcome_and_a_note_needs_text(): void
    {
        $this->signInAs('admin');
        $lead = $this->lead();

        $this->postJson("/api/leads/{$lead->id}/activities", ['type' => 'call'])->assertUnprocessable()->assertJsonValidationErrors('outcome');
        $this->postJson("/api/leads/{$lead->id}/activities", ['type' => 'note'])->assertUnprocessable()->assertJsonValidationErrors('body');
        $this->postJson("/api/leads/{$lead->id}/activities", ['type' => 'sms', 'body' => 'x'])->assertUnprocessable();
        $this->postJson("/api/leads/{$lead->id}/activities", ['type' => 'note', 'body' => 'Prefers evenings.'])->assertOk();
        $this->assertSame('New', $lead->fresh()->status, 'a note does not count as contacting the buyer');
    }

    public function test_follow_up_can_be_set_with_an_activity_and_cleared(): void
    {
        $this->signInAs('admin');
        $lead = $this->lead();
        $at = now()->addDays(2)->setTimezone('Asia/Dhaka')->toIso8601String();

        $this->postJson("/api/leads/{$lead->id}/activities", ['type' => 'call', 'outcome' => 'No answer', 'follow_up_at' => $at])->assertOk();
        $this->assertEqualsWithDelta(now()->addDays(2)->timestamp, $lead->fresh()->next_follow_up_at->timestamp, 5);

        $this->patchJson("/api/leads/{$lead->id}/follow-up", ['at' => null])->assertOk();
        $this->assertNull($lead->fresh()->next_follow_up_at);
    }

    public function test_stage_moves_are_logged_and_lost_needs_a_reason(): void
    {
        $this->signInAs('admin');
        $lead = $this->lead();

        $this->patchJson("/api/leads/{$lead->id}/stage", ['stage' => 'Negotiation'])->assertOk()->assertJsonPath('data.status', 'Negotiation');
        $this->patchJson("/api/leads/{$lead->id}/stage", ['stage' => 'Lost'])->assertUnprocessable()->assertJsonValidationErrors('lost_reason');
        $this->patchJson("/api/leads/{$lead->id}/stage", ['stage' => 'Lost', 'lost_reason' => 'Bought elsewhere'])->assertOk();
        $this->assertSame('Bought elsewhere', $lead->fresh()->lost_reason);
        $this->patchJson("/api/leads/{$lead->id}/stage", ['stage' => 'Qualified'])->assertOk();
        $this->assertNull($lead->fresh()->lost_reason, 'reopening a lost lead clears the reason');
        $this->patchJson("/api/leads/{$lead->id}/stage", ['stage' => 'Banana'])->assertUnprocessable();

        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'stage', 'body' => 'Reason: Bought elsewhere']);
    }

    public function test_leads_can_be_assigned_only_to_team_members(): void
    {
        $this->signInAs('admin');
        $lead = $this->lead();
        $agent = User::create(['name' => 'Tanvir Ahmed', 'email' => 'tanvir@example.test', 'password' => 'secret-password', 'role' => 'agent']);
        $buyer = User::create(['name' => 'A Buyer', 'email' => 'buyer@example.test', 'password' => 'secret-password', 'role' => 'buyer']);

        $this->patchJson("/api/leads/{$lead->id}/assign", ['user_id' => $buyer->id])->assertUnprocessable();
        $this->patchJson("/api/leads/{$lead->id}/assign", ['user_id' => $agent->id])->assertOk()->assertJsonPath('data.assigned_to', $agent->id);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'assign', 'body' => 'Assigned to Tanvir Ahmed']);
        $this->patchJson("/api/leads/{$lead->id}/assign", ['user_id' => null])->assertOk();
        $this->assertNull($lead->fresh()->assigned_to);

        $staff = collect($this->getJson('/api/leads/overview')->assertOk()->json('data.staff'))->pluck('name');
        $this->assertTrue($staff->contains('Tanvir Ahmed'));
        $this->assertFalse($staff->contains('A Buyer'));
    }

    public function test_scheduling_a_site_visit_creates_a_linked_viewing_and_moves_the_lead_to_site_visit(): void
    {
        $this->signInAs('admin');
        $lead = $this->lead(['status' => 'Qualified']);
        $guide = User::create(['name' => 'Abu Hanif', 'email' => 'hanif@example.test', 'password' => 'secret-password', 'role' => 'agent']);

        $this->postJson("/api/leads/{$lead->id}/visits", [
            'date' => $this->tomorrow(), 'time' => '15:30', 'visit_type' => 'Site visit', 'assigned_to' => $guide->id, 'vip_pickup' => true, 'pickup_location' => 'Gulshan 1',
        ])->assertCreated()->assertJsonPath('data.time', '15:30')->assertJsonPath('data.assigned_agent', 'Abu Hanif');

        $viewing = Viewing::sole();
        $this->assertSame($lead->id, $viewing->lead_id);
        $this->assertSame('Test Buyer', $viewing->name);
        $this->assertSame('03:30 PM', $viewing->scheduled_time);
        $this->assertSame('Confirmed', $viewing->status);
        // 15:30 in Dhaka (UTC+6) is 09:30 UTC.
        $this->assertSame('09:30', $viewing->scheduled_at->format('H:i'));
        $this->assertSame('Site Visit', $lead->fresh()->status);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'visit']);

        $upcoming = $this->getJson('/api/leads/overview')->json('data.visits');
        $this->assertSame([$lead->id], collect($upcoming)->pluck('lead_id')->all());
        $this->assertCount(1, $this->getJson("/api/leads/{$lead->id}/crm")->json('data.visits'));
    }

    public function test_a_visit_cannot_be_booked_in_the_past_or_with_bad_times(): void
    {
        $this->signInAs('admin');
        $lead = $this->lead();

        $this->postJson("/api/leads/{$lead->id}/visits", ['date' => now('Asia/Dhaka')->subDay()->toDateString(), 'time' => '10:00', 'visit_type' => 'Site visit'])->assertUnprocessable()->assertJsonValidationErrors('date');
        $this->postJson("/api/leads/{$lead->id}/visits", ['date' => $this->tomorrow(), 'time' => '25:00', 'visit_type' => 'Site visit'])->assertUnprocessable()->assertJsonValidationErrors('time');
        $this->postJson("/api/leads/{$lead->id}/visits", ['date' => $this->tomorrow(), 'time' => '10:00', 'visit_type' => 'Picnic'])->assertUnprocessable();
        $this->assertDatabaseCount('viewings', 0);
        $this->assertSame('New', $lead->fresh()->status);
    }

    public function test_a_visit_can_be_rescheduled_completed_and_only_through_its_own_lead(): void
    {
        $this->signInAs('admin');
        $lead = $this->lead();
        $other = $this->lead(['name' => 'Someone Else']);
        $visitId = $this->postJson("/api/leads/{$lead->id}/visits", ['date' => $this->tomorrow(), 'time' => '10:00', 'visit_type' => 'Video call'])->json('data.id');

        $newDate = now('Asia/Dhaka')->addDays(3)->toDateString();
        $this->patchJson("/api/leads/{$lead->id}/visits/{$visitId}", ['date' => $newDate, 'time' => '16:00'])->assertOk()->assertJsonPath('data.date', $newDate)->assertJsonPath('data.time', '16:00');
        $this->patchJson("/api/leads/{$other->id}/visits/{$visitId}", ['status' => 'Cancelled'])->assertNotFound();

        $this->patchJson("/api/leads/{$lead->id}/visits/{$visitId}", ['status' => 'Completed', 'outcome_notes' => 'Liked the plot, asked for payment plan.'])->assertOk()->assertJsonPath('data.status', 'Completed');
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'note', 'body' => 'Visit outcome: Liked the plot, asked for payment plan.']);
        $this->assertSame([], $this->getJson('/api/leads/overview')->json('data.visits'), 'completed visits are no longer upcoming');
        $this->patchJson("/api/leads/{$lead->id}/visits/{$visitId}", ['status' => 'Teleported'])->assertUnprocessable();
    }
}
