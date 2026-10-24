<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class MetaLeadWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-app-secret';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.meta.app_secret' => self::SECRET,
            'services.meta.webhook_verify_token' => 'verify-me',
            'services.meta.page_access_token' => 'page-token',
        ]);
    }

    /** @return array<string, mixed> */
    private function graphLead(string $id = '1111843911831334', array $overrides = []): array
    {
        return array_merge([
            'id' => $id,
            'created_time' => '2026-10-24T05:32:21+0000',
            'ad_id' => '120248328950180656',
            'ad_name' => 'A - Urgent Sale Facts + Price | Video 75s',
            'adset_id' => '120248322688230656',
            'adset_name' => 'Dhaka 20km around Uttara S12 | Broad A+ | Instant Form',
            'campaign_id' => '120248322687710656',
            'campaign_name' => 'GBREL | Uttara S12 Rd18 Flat | Leads | Instant Form | Oct26',
            'form_id' => '1618000993676451',
            'platform' => 'fb',
            'is_organic' => false,
            'field_data' => [
                ['name' => 'আপনি_কবের_মধ্যে_কেনার_সক্ষমতা_রাখেন?', 'values' => ['এই_মাসের_মধ্যে_কিনতে_পারব']],
                ['name' => 'কীভাবে_পেমেন্ট_করার_পরিকল্পনা_করছেন?', 'values' => ['নিজস্ব_অর্থ_+_ব্যাংক_লোন']],
                ['name' => 'email', 'values' => ['buyer@example.com']],
                ['name' => 'full_name', 'values' => ['Kazi Eunus Ahmed']],
                ['name' => 'phone_number', 'values' => ['01785665175']],
            ],
        ], $overrides);
    }

    private function fakeGraph(array ...$leads): void
    {
        $byId = collect($leads)->keyBy('id');
        Http::fake(function (HttpRequest $request) use ($byId, $leads) {
            $path = parse_url($request->url(), PHP_URL_PATH);
            if (str_ends_with($path, '/1618000993676451/leads')) {
                return Http::response(['data' => $leads, 'paging' => []]);
            }
            if (str_ends_with($path, '/1618000993676451')) {
                return Http::response(['id' => '1618000993676451', 'name' => 'GBREL Uttara S12 Rd18 Flat - Site Visit (Higher Intent)']);
            }
            $id = basename($path);

            return $byId->has($id) ? Http::response($byId[$id]) : Http::response(['error' => ['message' => 'Unknown lead']], 404);
        });
    }

    private function webhook(array $leadgenIds, ?string $signature = null)
    {
        $body = json_encode(['object' => 'page', 'entry' => [[
            'id' => '612345678901234',
            'time' => time(),
            'changes' => array_map(fn ($id) => ['field' => 'leadgen', 'value' => ['leadgen_id' => $id, 'form_id' => '1618000993676451', 'page_id' => '612345678901234']], $leadgenIds),
        ]]]);
        $signature ??= 'sha256='.hash_hmac('sha256', $body, self::SECRET);

        return $this->call('POST', '/api/meta/leadgen', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_HUB_SIGNATURE_256' => $signature], $body);
    }

    public function test_meta_handshake_returns_the_challenge_only_for_the_right_token(): void
    {
        $this->get('/api/meta/leadgen?hub.mode=subscribe&hub.verify_token=verify-me&hub.challenge=12345')
            ->assertOk()->assertSee('12345', false);
        $this->get('/api/meta/leadgen?hub.mode=subscribe&hub.verify_token=wrong&hub.challenge=12345')->assertForbidden();
    }

    public function test_a_webhook_lead_is_saved_in_the_crm_with_its_form_answers_and_ad(): void
    {
        $this->fakeGraph($this->graphLead());

        $this->webhook(['1111843911831334'])->assertOk()->assertJson(['queued' => 1]);

        $lead = Lead::where('meta_lead_id', '1111843911831334')->firstOrFail();
        $this->assertSame('Kazi Eunus Ahmed', $lead->name);
        $this->assertSame('+8801785665175', $lead->phone);
        $this->assertSame('buyer@example.com', $lead->email);
        $this->assertSame('New', $lead->status);
        $this->assertSame('GBREL Uttara S12 Rd18 Flat - Site Visit (Higher Intent)', $lead->property_title);
        $this->assertSame('facebook', $lead->utm_source);
        $this->assertSame('paid_social', $lead->utm_medium);
        $this->assertSame('GBREL | Uttara S12 Rd18 Flat | Leads | Instant Form | Oct26', $lead->utm_campaign);
        $this->assertSame('A - Urgent Sale Facts + Price | Video 75s', $lead->utm_content);
        $this->assertSame('1618000993676451', $lead->meta_form_id);
        $this->assertSame('এই মাসের মধ্যে কিনতে পারব', $lead->meta_answers[0]['answer']);
        $this->assertStringContainsString('কীভাবে পেমেন্ট করার পরিকল্পনা করছেন?: নিজস্ব অর্থ + ব্যাংক লোন', $lead->message);
        $this->assertSame('2026-10-24 05:32:21', $lead->created_at->utc()->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'type' => 'meta_lead']);

        $this->signInAs('admin');
        $this->getJson('/api/leads')->assertOk()->assertJsonPath('data.0.meta_lead_id', '1111843911831334');
    }

    public function test_a_retried_webhook_does_not_create_the_lead_twice(): void
    {
        $this->fakeGraph($this->graphLead());

        $this->webhook(['1111843911831334'])->assertOk();
        $this->webhook(['1111843911831334'])->assertOk();

        $this->assertSame(1, Lead::where('meta_lead_id', '1111843911831334')->count());
    }

    public function test_an_unsigned_or_forged_webhook_is_rejected(): void
    {
        Http::fake();

        $this->webhook(['1111843911831334'], 'sha256=forged')->assertForbidden();
        $this->webhook(['1111843911831334'], '')->assertForbidden();

        Http::assertNothingSent();
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_a_graph_api_failure_is_logged_and_does_not_break_the_webhook(): void
    {
        $this->fakeGraph();

        $this->webhook(['999999999999'])->assertOk();

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_instagram_leads_are_marked_as_instagram(): void
    {
        $this->fakeGraph($this->graphLead('2222222222222', ['platform' => 'ig']));

        $this->webhook(['2222222222222'])->assertOk();

        $lead = Lead::where('meta_lead_id', '2222222222222')->firstOrFail();
        $this->assertSame('instagram', $lead->utm_source);
        $this->assertSame('Instagram Lead Ad', $lead->lead_type);
    }

    public function test_sync_command_reads_every_page_form_and_only_asks_for_new_leads_after_the_first_run(): void
    {
        $lead = $this->graphLead();
        Http::fake(function (HttpRequest $request) use ($lead) {
            $path = parse_url($request->url(), PHP_URL_PATH);
            if (str_ends_with($path, '/me/leadgen_forms')) {
                return Http::response(['data' => [
                    ['id' => '1618000993676451', 'name' => 'GBREL Uttara S12 Rd18 Flat - Site Visit (Higher Intent)', 'status' => 'ACTIVE'],
                    ['id' => '910884975294742', 'name' => 'Old owner form', 'status' => 'ARCHIVED'],
                ]]);
            }
            if (str_ends_with($path, '/1618000993676451/leads')) {
                return Http::response(['data' => [$lead]]);
            }

            return Http::response(['data' => []]);
        });

        $this->artisan('meta:sync-leads')->expectsOutputToContain('Checked 2 forms: 1 new leads')->assertSuccessful();
        $this->artisan('meta:sync-leads')->expectsOutputToContain('Checked 1 forms: 0 new leads, 1 already in the CRM')->assertSuccessful();

        $this->assertSame(1, Lead::where('meta_lead_id', '1111843911831334')->count());
        $this->assertSame('GBREL Uttara S12 Rd18 Flat - Site Visit (Higher Intent)', Lead::first()->property_title);
        Http::assertSent(fn (HttpRequest $request) => str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/leads') && isset($request['filtering']));
    }

    public function test_a_user_token_is_swapped_for_the_page_token_when_the_page_id_is_set(): void
    {
        config(['services.meta.page_access_token' => 'user-token', 'services.meta.page_id' => '1773471184786371']);
        $lead = $this->graphLead();
        Http::fake(function (HttpRequest $request) use ($lead) {
            $path = parse_url($request->url(), PHP_URL_PATH);
            if (str_ends_with($path, '/1773471184786371') && $request['access_token'] === 'user-token') {
                return Http::response(['access_token' => 'page-token-from-user', 'id' => '1773471184786371']);
            }
            if ($request['access_token'] !== 'page-token-from-user') {
                return Http::response(['error' => ['message' => '(#100) Tried accessing nonexisting field (leadgen_forms)']], 400);
            }
            if (str_ends_with($path, '/1773471184786371/leadgen_forms')) {
                return Http::response(['data' => [['id' => '1618000993676451', 'name' => 'Site Visit form', 'status' => 'ACTIVE']]]);
            }

            return Http::response(['data' => str_ends_with($path, '/1618000993676451/leads') ? [$lead] : []]);
        });

        $this->artisan('meta:sync-leads')->expectsOutputToContain('Checked 1 forms: 1 new leads')->assertSuccessful();

        $this->assertSame(1, Lead::whereNotNull('meta_lead_id')->count());
    }

    public function test_a_wrong_page_id_gives_a_clear_error_instead_of_reusing_the_user_token(): void
    {
        config(['services.meta.page_id' => '123']);
        Http::fake(['*' => Http::response(['error' => ['message' => 'Unsupported get request.']], 400)]);

        $this->artisan('meta:sync-leads')->expectsOutputToContain('Could not get the Page token for META_PAGE_ID 123')->assertFailed();
    }

    public function test_meta_check_reports_the_token_type_and_forms(): void
    {
        Http::fake(function (HttpRequest $request) {
            $path = parse_url($request->url(), PHP_URL_PATH);

            return str_ends_with($path, '/me')
                ? Http::response(['id' => '1773471184786371', 'name' => 'Gram Bangla Real Estate Limited', 'metadata' => ['type' => 'page']])
                : Http::response(['data' => [['id' => '1618000993676451', 'name' => 'Site Visit form', 'status' => 'ACTIVE']]]);
        });

        $this->artisan('meta:check')
            ->expectsOutputToContain('Token belongs to: page "Gram Bangla Real Estate Limited"')
            ->expectsOutputToContain('Lead forms found: 1')
            ->assertSuccessful();
    }

    public function test_a_user_token_without_a_page_id_gets_a_clear_error(): void
    {
        Http::fake(['*' => Http::response(['error' => ['message' => '(#100) Tried accessing nonexisting field (leadgen_forms)']], 400)]);

        $this->artisan('meta:sync-leads')->expectsOutputToContain('set META_PAGE_ID')->assertFailed();
    }

    public function test_sync_command_does_nothing_without_a_page_token(): void
    {
        config(['services.meta.page_access_token' => null]);
        Http::fake();

        $this->artisan('meta:sync-leads')->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_backfill_command_imports_a_whole_form_and_skips_known_leads(): void
    {
        $this->fakeGraph($this->graphLead('1111843911831334'), $this->graphLead('1432904612243360', ['field_data' => [
            ['name' => 'full_name', 'values' => ['mydul Islam riad']],
            ['name' => 'phone_number', 'values' => ['+8801710794185']],
        ]]));

        $this->artisan('meta:import-leads', ['forms' => ['1618000993676451']])
            ->expectsOutputToContain('2 new leads imported, 0 already in the CRM')
            ->assertSuccessful();
        $this->artisan('meta:import-leads', ['forms' => ['1618000993676451']])
            ->expectsOutputToContain('0 new leads imported, 2 already in the CRM')
            ->assertSuccessful();

        $this->assertSame(2, Lead::whereNotNull('meta_lead_id')->count());
        Http::assertSent(fn (HttpRequest $request) => $request['access_token'] === 'page-token');
    }
}
