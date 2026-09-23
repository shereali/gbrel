<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class PropertyLeadTest extends TestCase
{
    use RefreshDatabase;

    private function inquiry(): array
    {
        $property = Property::create([
            'title' => 'Test land share', 'address' => 'Test address', 'area_name' => 'Test area', 'price' => 4200000,
        ]);

        return [
            'name' => 'Test Buyer', 'phone' => '০১৭১২৩৪৫৬৭৮', 'property_id' => $property->id,
            'property_title' => 'Untrusted client title', 'buyer_category' => 'Own / family use',
            'investment_readiness' => 'Within 30 days', 'budget_range' => 'Listed price fits budget',
            'preferred_contact' => 'Phone Call', 'form_version' => 'property_inquiry_v1',
            'contact_consent' => true, 'request_id' => (string) Str::uuid(),
            'utm_source' => 'facebook', 'utm_campaign' => 'test-property', 'utm_content' => 'creative-1',
            'callback_time' => 'Evening', 'next_step' => 'Site visit', 'status' => 'Converted',
        ];
    }

    public function test_stores_qualification_consent_and_attribution_and_normalizes_phone(): void
    {
        $payload = $this->inquiry();
        $response = $this->postJson('/api/leads', $payload)->assertCreated()->assertJsonPath('success', true);
        $this->assertDatabaseHas('leads', [
            'id' => $response->json('data.id'), 'phone' => '+8801712345678', 'property_title' => 'Test land share',
            'status' => 'New', 'budget_range' => 'Listed price fits budget', 'utm_content' => 'creative-1',
            'callback_time' => 'Evening', 'next_step' => 'Site visit',
        ]);
        $this->assertNotNull(Lead::first()->contact_consented_at);
        $response->assertJsonMissingPath('data.phone');
    }

    public function test_retry_does_not_create_a_second_lead(): void
    {
        $payload = $this->inquiry();
        $first = $this->postJson('/api/leads', $payload)->assertCreated();
        $this->postJson('/api/leads', $payload)->assertOk()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertDatabaseCount('leads', 1);
    }

    public function test_rejects_invalid_contact_missing_consent_and_missing_answers(): void
    {
        $payload = $this->inquiry();
        $payload['phone'] = '123';
        $payload['contact_consent'] = false;
        unset($payload['budget_range']);
        $this->postJson('/api/leads', $payload)->assertUnprocessable()->assertJsonValidationErrors(['phone', 'contact_consent', 'budget_range']);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_existing_general_inquiry_does_not_invent_buyer_readiness(): void
    {
        $this->postJson('/api/leads', ['name' => 'General Buyer', 'phone' => '+44 7700 900123', 'message' => 'Please call'])
            ->assertCreated();
        $this->assertDatabaseHas('leads', ['name' => 'General Buyer', 'buyer_category' => null, 'investment_readiness' => null]);
    }

    public function test_public_and_buyer_access_cannot_read_or_change_leads(): void
    {
        $this->getJson('/api/leads')->assertUnauthorized();
        $this->getJson('/api/admin/stats')->assertUnauthorized();
        $this->patchJson('/api/leads/1/stage', ['stage' => 'Converted'])->assertUnauthorized();
        $this->deleteJson('/api/leads/1')->assertUnauthorized();
        $buyer = User::factory()->create(['role' => 'buyer']);
        Cache::put('gbrel_auth_token_test-buyer', $buyer->id, 60);
        $this->withToken('test-buyer')->getJson('/api/leads')->assertForbidden();
    }

    public function test_admin_can_read_leads_and_suspended_admin_cannot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Cache::put('gbrel_auth_token_test-admin', $admin->id, 60);
        $this->withToken('test-admin')->getJson('/api/leads')->assertOk();
        $admin->update(['status' => 'Suspended']);
        $this->withToken('test-admin')->getJson('/api/leads')->assertForbidden();
    }

    public function test_rejects_invalid_property_and_rate_limits_abuse(): void
    {
        $payload = $this->inquiry();
        $payload['property_id'] = 999999;
        $this->postJson('/api/leads', $payload)->assertUnprocessable()->assertJsonValidationErrors('property_id');
        for ($i = 0; $i < 9; $i++) {
            $this->postJson('/api/leads', [])->assertUnprocessable();
        }
        $this->postJson('/api/leads', [])->assertTooManyRequests();
    }
}
