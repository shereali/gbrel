<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ManualLeadTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function inquiry(array $overrides = []): array
    {
        return array_merge(['name' => 'Rahim Uddin', 'phone' => '01819-123456', 'channel' => 'Phone call', 'property_title' => 'Uttara S12 flat'], $overrides);
    }

    public function test_only_staff_who_can_manage_leads_can_log_one(): void
    {
        $this->postJson('/api/leads/manual', $this->inquiry())->assertUnauthorized();
        $this->signInAs('buyer');
        $this->postJson('/api/leads/manual', $this->inquiry())->assertForbidden();
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_a_phone_call_is_saved_as_a_contacted_manual_lead_with_its_score_and_answers(): void
    {
        $admin = $this->signInAs('admin');
        $agent = User::create(['name' => 'Tanvir Ahmed', 'email' => 'tanvir@example.test', 'password' => 'secret-password', 'role' => 'agent']);

        $this->postJson('/api/leads/manual', $this->inquiry([
            'buyer_category' => 'Investment', 'investment_readiness' => 'Within 30 days', 'budget_range' => 'Listed price fits budget',
            'payment' => 'Own funds, lump sum', 'residence' => 'Abroad (NRB)', 'callback_time' => 'সন্ধ্যা ৫টা–রাত ৯টা',
            'lead_score' => 'HOT (9/10)', 'answered' => 5, 'total' => 5, 'assigned_to' => $agent->id,
            'follow_up_at' => now()->addDay()->toIso8601String(), 'notes' => 'Wants the south-facing unit.',
        ]))->assertCreated();

        $lead = Lead::sole();
        $this->assertSame('+8801819123456', $lead->phone);
        $this->assertSame('Contacted', $lead->status);
        $this->assertSame('crm_manual', $lead->utm_source);
        $this->assertSame('Phone call', $lead->utm_medium);
        $this->assertSame($agent->id, $lead->assigned_to);
        $this->assertNotNull($lead->last_contacted_at);
        $this->assertNotNull($lead->next_follow_up_at);
        $this->assertNull($lead->visitor_id, 'a manual lead is never tied to the staff member\'s own browsing');
        $this->assertStringContainsString('Lead score: HOT (9/10)', $lead->message);
        $this->assertStringContainsString('Lives: Abroad (NRB)', $lead->message);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'manual', 'user_id' => $admin->id, 'body' => 'Wants the south-facing unit.']);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'assign']);

        $listed = collect($this->getJson('/api/leads')->json('data'))->firstWhere('id', $lead->id);
        $this->assertSame('Wants the south-facing unit.', $listed['last_activity']['body']);
    }

    public function test_a_referral_stays_new_until_someone_calls(): void
    {
        $this->signInAs('admin');
        $this->postJson('/api/leads/manual', $this->inquiry(['channel' => 'Referral']))->assertCreated();
        $this->assertSame('New', Lead::sole()->status);
        $this->assertNull(Lead::sole()->last_contacted_at);
    }

    public function test_a_number_already_in_the_crm_is_flagged_unless_the_team_confirms(): void
    {
        $this->signInAs('admin');
        $existing = Lead::create(['name' => 'Rahim', 'phone' => '+8801819123456', 'status' => 'Qualified', 'property_title' => 'Lake view']);

        $this->postJson('/api/leads/manual', $this->inquiry())->assertStatus(409)
            ->assertJsonPath('data.duplicate.id', $existing->id)->assertJsonPath('data.duplicate.status', 'Qualified');
        $this->assertDatabaseCount('leads', 1);

        $this->postJson('/api/leads/manual', $this->inquiry(['allow_duplicate' => true]))->assertCreated();
        $this->assertDatabaseCount('leads', 2);
    }

    public function test_bad_input_gets_a_clear_reason(): void
    {
        $this->signInAs('admin');
        $buyer = User::create(['name' => 'A Buyer', 'email' => 'buyer@example.test', 'password' => 'secret-password', 'role' => 'buyer']);

        $this->postJson('/api/leads/manual', $this->inquiry(['phone' => '1819123']))->assertUnprocessable()
            ->assertJsonPath('errors.phone.0', 'Enter a valid mobile number, e.g. 01712345678, or a foreign number with its country code (+…).');
        $this->postJson('/api/leads/manual', $this->inquiry(['channel' => 'Pigeon']))->assertUnprocessable()->assertJsonValidationErrors('channel');
        $this->postJson('/api/leads/manual', $this->inquiry(['assigned_to' => $buyer->id]))->assertUnprocessable();
        $this->assertDatabaseCount('leads', 0);
    }
}
