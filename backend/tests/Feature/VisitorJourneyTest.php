<?php

namespace Tests\Feature;

use App\Jobs\SendMetaEvent;
use App\Models\Lead;
use App\Models\LeadDraft;
use App\Models\Property;
use App\Models\VisitorEvent;
use App\Models\VisitorSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class VisitorJourneyTest extends TestCase
{
    use RefreshDatabase;

    private const VID = 'vid0123456789abcdef0123456789ab';

    private const SID = 'sid0123456789abcdef0123456789ab';

    private function batch(array $events, array $extra = []): string
    {
        return json_encode(array_merge([
            'vid' => self::VID, 'sid' => self::SID, 'fbp' => 'fb.1.123.456',
            'src' => ['utm_source' => 'facebook', 'utm_campaign' => 'camp', 'utm_content' => 'ad-one', 'fb_ad_id' => '123abc456', 'fbclid' => 'ABC'],
            'events' => $events,
        ], $extra));
    }

    private function send(string $body)
    {
        return $this->call('POST', '/api/t', [], [], [], ['CONTENT_TYPE' => 'text/plain;charset=UTF-8', 'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 13) Mobile FBAV/400'], $body);
    }

    private function inquiry(): array
    {
        $property = Property::create(['title' => 'Test land share', 'address' => 'Test address', 'area_name' => 'Test area', 'price' => 4200000]);

        return [
            'name' => 'Test Buyer', 'phone' => '01712345678', 'property_id' => $property->id,
            'buyer_category' => 'Own / family use', 'investment_readiness' => 'Within 30 days',
            'budget_range' => 'Listed price fits budget', 'preferred_contact' => 'Phone Call',
            'form_version' => 'property_inquiry_v1', 'contact_consent' => true, 'request_id' => (string) Str::uuid(),
            'message' => "Lead score: HOT (8/9)\nPayment plan: Own funds",
            'vid' => self::VID, 'sid' => self::SID,
        ];
    }

    public function test_events_create_one_visit_with_ad_source_and_rollups(): void
    {
        $this->send($this->batch([
            ['e' => 'page_view', 't' => now()->getTimestampMs(), 'p' => '/properties/6', 'pid' => '6', 'l' => 'Plot'],
            ['e' => 'scroll', 'v' => 50],
            ['e' => 'engaged_time', 'v' => 15],
            ['e' => 'survey_step', 'st' => 2, 'l' => 'timeline', 'v' => 'Within 30 days', 'm' => ['step' => 2, 'nested' => ['x' => 1]]],
            ['e' => 'hack_the_planet'],
        ]))->assertNoContent();
        $this->send($this->batch([['e' => 'survey_step', 'st' => 1]]))->assertNoContent();

        $session = VisitorSession::sole();
        $this->assertSame('ad-one', $session->utm_content);
        $this->assertSame('123456', $session->fb_ad_id, 'ad ids keep digits only');
        $this->assertSame('mobile', $session->device);
        $this->assertTrue($session->in_facebook_app);
        $this->assertSame(50, $session->max_scroll);
        $this->assertSame(15, $session->seconds_active);
        $this->assertSame(5, $session->events_count);
        $this->assertSame('survey_step_2', $session->furthest_step, 'an earlier step never lowers the furthest step');
        $this->assertSame(['step' => 2], VisitorEvent::where('event', 'survey_step')->orderBy('id')->first()->meta);
        $this->assertDatabaseMissing('visitor_events', ['event' => 'hack_the_planet']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $session->ip_hash);
    }

    public function test_bad_ids_and_oversized_bodies_store_nothing(): void
    {
        $this->send(json_encode(['vid' => '<x>', 'sid' => self::SID, 'events' => [['e' => 'page_view']]]))->assertNoContent();
        $this->send(str_repeat('a', 70000))->assertNoContent();
        $this->assertDatabaseCount('visitor_sessions', 0);
        $this->assertDatabaseCount('visitor_events', 0);
    }

    public function test_another_visitor_cannot_write_into_an_existing_visit(): void
    {
        $this->send($this->batch([['e' => 'page_view']]))->assertNoContent();
        $this->send($this->batch([['e' => 'page_view']], ['vid' => 'other0123456789abcdef0123456789']))->assertNoContent();
        $this->assertDatabaseCount('visitor_events', 1);
    }

    public function test_draft_needs_consent_and_a_valid_phone_and_keeps_one_row_per_property(): void
    {
        $payload = ['vid' => self::VID, 'sid' => self::SID, 'phone' => '০১৭১২৩৪৫৬৭৮', 'name' => 'Rahim', 'answers' => ['budget' => 'ready'], 'step' => 2, 'consent' => true];

        $this->postJson('/api/t/draft', array_merge($payload, ['consent' => false]))->assertUnprocessable();
        $this->postJson('/api/t/draft', array_merge($payload, ['phone' => '123']))->assertUnprocessable();
        $this->postJson('/api/t/draft', $payload)->assertOk();
        $this->postJson('/api/t/draft', array_merge($payload, ['step' => 3]))->assertOk();

        $this->assertDatabaseCount('lead_drafts', 1);
        $this->assertDatabaseHas('lead_drafts', ['phone' => '+8801712345678', 'last_step' => 3, 'converted_lead_id' => null]);
    }

    public function test_saved_lead_is_linked_to_the_journey_and_sent_to_meta_with_the_browser_event_ids(): void
    {
        config(['services.meta.capi_token' => 'test-token', 'services.meta.pixel_id' => '1234567890123456']);
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $this->send($this->batch([['e' => 'survey_step', 'st' => 1]]))->assertNoContent();
        $this->postJson('/api/t/draft', ['vid' => self::VID, 'sid' => self::SID, 'phone' => '01712345678', 'consent' => true])->assertOk();

        $payload = $this->inquiry();
        $leadId = $this->withHeader('Referer', 'https://gbrel.com/properties/6')->postJson('/api/leads', $payload)->assertCreated()->json('data.id');

        $lead = Lead::findOrFail($leadId);
        $this->assertSame(self::VID, $lead->visitor_id);
        $this->assertSame($leadId, VisitorSession::sole()->lead_id);
        $this->assertSame('lead', VisitorSession::sole()->furthest_step);
        $this->assertSame($leadId, LeadDraft::sole()->converted_lead_id);
        $this->assertDatabaseHas('visitor_events', ['event' => 'lead_saved', 'label' => 'HOT']);

        $sent = [];
        Http::assertSent(function ($request) use (&$sent) {
            $event = $request['data'][0];
            $sent[$event['event_name']] = $event;

            return str_contains($request->url(), '/1234567890123456/events');
        });
        $this->assertSame($payload['request_id'], $sent['Lead']['event_id']);
        $this->assertSame($payload['request_id'].'-q', $sent['QualifiedLead']['event_id']);
        $this->assertSame([hash('sha256', '8801712345678')], $sent['Lead']['user_data']['ph']);
        $this->assertSame([hash('sha256', 'test')], $sent['Lead']['user_data']['fn']);
        $this->assertSame('fb.1.123.456', $sent['Lead']['user_data']['fbp']);
        $this->assertSame('https://gbrel.com/properties/6', $sent['Lead']['event_source_url']);
    }

    public function test_lead_from_a_visitor_without_tracking_is_not_sent_to_meta(): void
    {
        config(['services.meta.capi_token' => 'test-token', 'services.meta.pixel_id' => '1234567890123456']);
        Http::fake();
        $payload = $this->inquiry();
        unset($payload['vid'], $payload['sid']);

        $this->postJson('/api/leads', $payload)->assertCreated();

        Http::assertNothingSent();
        $this->assertNull(Lead::first()->visitor_id);
    }

    public function test_a_meta_outage_never_fails_the_lead(): void
    {
        config(['services.meta.capi_token' => 'test-token', 'services.meta.pixel_id' => '1234567890123456']);
        Http::fake(['graph.facebook.com/*' => Http::response('down', 500)]);

        $this->postJson('/api/leads', $this->inquiry())->assertCreated();

        $this->assertDatabaseCount('leads', 1);
        Http::assertSentCount(4); // Lead and QualifiedLead, two attempts each
    }

    public function test_a_retried_request_is_not_sent_to_meta_twice(): void
    {
        Bus::fake();
        $payload = $this->inquiry();

        $this->postJson('/api/leads', $payload)->assertCreated();
        $this->postJson('/api/leads', $payload)->assertOk();

        Bus::assertDispatchedAfterResponseTimes(SendMetaEvent::class, 2); // Lead + QualifiedLead, first request only
    }

    public function test_journey_is_staff_only_and_shows_the_visit_timeline(): void
    {
        $this->send($this->batch([['e' => 'page_view', 'p' => '/properties/6'], ['e' => 'cta_click', 'l' => 'Book']]))->assertNoContent();
        $leadId = $this->postJson('/api/leads', $this->inquiry())->assertCreated()->json('data.id');

        $this->getJson("/api/leads/{$leadId}/journey")->assertUnauthorized();
        $this->signInAs('buyer');
        $this->getJson("/api/leads/{$leadId}/journey")->assertForbidden();

        $this->signInAs('admin');
        $this->getJson("/api/leads/{$leadId}/journey")
            ->assertOk()
            ->assertJsonPath('data.lead.tier', 'HOT')
            ->assertJsonPath('data.sessions.0.ad', 'ad-one')
            ->assertJsonPath('data.sessions.0.events.0.event', 'page_view')
            ->assertJsonPath('data.sessions.0.events.1.label', 'Book');
    }

    public function test_prune_removes_old_anonymous_visits_but_keeps_visits_that_became_leads(): void
    {
        $old = now()->subDays(200);
        VisitorSession::create(['session_id' => 'anonymous-visit-1', 'visitor_id' => 'v-anonymous-1', 'started_at' => $old]);
        VisitorSession::create(['session_id' => 'lead-visit-000001', 'visitor_id' => 'v-lead-000001', 'started_at' => $old, 'lead_id' => 1]);
        VisitorEvent::create(['session_id' => 'anonymous-visit-1', 'visitor_id' => 'v-anonymous-1', 'event' => 'page_view', 'occurred_at' => $old]);
        VisitorEvent::create(['session_id' => 'lead-visit-000001', 'visitor_id' => 'v-lead-000001', 'event' => 'page_view', 'occurred_at' => $old]);

        $this->artisan('tracking:prune')->assertSuccessful();

        $this->assertSame(['lead-visit-000001'], VisitorSession::pluck('session_id')->all());
        $this->assertSame(['lead-visit-000001'], VisitorEvent::pluck('session_id')->all());
    }
}
