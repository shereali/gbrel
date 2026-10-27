<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class LeadHighlightTest extends TestCase
{
    use RefreshDatabase;

    private const CALL_TIME = 'আমাদের প্রতিনিধি কখন কল করলে আপনার সুবিধা হবে?';

    private const PAYMENT = 'কীভাবে পেমেন্ট করার পরিকল্পনা করছেন?';

    private function metaLead(): Lead
    {
        return Lead::create([
            'name' => 'Kazi Eunus Ahmed', 'phone' => '+8801785665175', 'status' => 'New',
            'meta_lead_id' => '1111843911831334', 'meta_form_id' => '1618000993676451',
            'meta_form_name' => 'GBREL Uttara S12 Rd18 Flat - Site Visit (Higher Intent)',
            'meta_answers' => [
                ['question' => self::PAYMENT, 'answer' => 'নিজস্ব অর্থ + ব্যাংক লোন'],
                ['question' => self::CALL_TIME, 'answer' => 'যত দ্রুত সম্ভব (আজই)'],
            ],
        ]);
    }

    public function test_forms_list_their_fields_and_highlight_the_call_time_by_default(): void
    {
        $this->metaLead();
        $this->signInAs('admin');

        $data = $this->getJson('/api/leads/highlights')->assertOk()->json('data');

        $form = collect($data['forms'])->firstWhere('key', '1618000993676451');
        $this->assertSame('GBREL Uttara S12 Rd18 Flat - Site Visit (Higher Intent)', $form['name']);
        $this->assertEqualsCanonicalizing([self::PAYMENT, self::CALL_TIME], $form['fields']);
        $this->assertSame([self::CALL_TIME], $data['selected']['1618000993676451']);
        $this->assertSame(['Best time to call'], $data['selected']['website']);
    }

    public function test_staff_can_choose_several_fields_and_an_empty_choice_is_kept(): void
    {
        $this->metaLead();
        $this->signInAs('admin');

        $this->putJson('/api/leads/highlights', ['form' => '1618000993676451', 'fields' => [self::CALL_TIME, self::PAYMENT]])->assertOk();
        $this->assertSame([self::CALL_TIME, self::PAYMENT], $this->getJson('/api/leads/highlights')->json('data.selected.1618000993676451'));

        $this->putJson('/api/leads/highlights', ['form' => 'website', 'fields' => []])->assertOk();
        $this->assertSame([], $this->getJson('/api/leads/highlights')->json('data.selected.website'));
    }

    public function test_only_lead_managers_can_change_highlights(): void
    {
        $this->getJson('/api/leads/highlights')->assertUnauthorized();
        $this->signInAs('buyer');
        $this->getJson('/api/leads/highlights')->assertForbidden();
        $this->putJson('/api/leads/highlights', ['form' => 'website', 'fields' => []])->assertForbidden();
    }

    public function test_website_projects_are_listed_and_can_have_custom_highlights(): void
    {
        Lead::create([
            'name' => 'Rahim Uddin',
            'phone' => '+8801712345678',
            'status' => 'New',
            'property_title' => 'Purbachal Marine City',
        ]);
        $this->signInAs('admin');

        $data = $this->getJson('/api/leads/highlights')->assertOk()->json('data');
        $websitePropForm = collect($data['forms'])->firstWhere('key', 'website:Purbachal Marine City');
        $this->assertNotNull($websitePropForm);
        $this->assertSame('Website - Purbachal Marine City', $websitePropForm['name']);

        // Default inherits website highlights
        $this->assertSame(['Best time to call'], $data['selected']['website:Purbachal Marine City']);

        // Staff can customize specific website project highlights
        $this->putJson('/api/leads/highlights', [
            'form' => 'website:Purbachal Marine City',
            'fields' => ['Budget', 'Best time to call'],
        ])->assertOk();

        $updated = $this->getJson('/api/leads/highlights')->json('data.selected');
        $this->assertSame(['Budget', 'Best time to call'], $updated['website:Purbachal Marine City']);
        // Base website remains unchanged
        $this->assertSame(['Best time to call'], $updated['website']);
    }
}
