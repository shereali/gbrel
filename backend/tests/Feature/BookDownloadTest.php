<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class BookDownloadTest extends TestCase
{
    use RefreshDatabase;

    private function reader(array $overrides = []): array
    {
        return [
            'name' => 'Test Reader', 'phone' => '০১৭১২৩৪৫৬৭৮', 'reason' => 'buy',
            'contact_consent' => true, 'request_id' => (string) Str::uuid(),
            'utm_source' => 'facebook', 'utm_campaign' => 'land-book', 'utm_content' => 'video-hook-1',
            ...$overrides,
        ];
    }

    public function test_saves_the_reader_as_a_lead_and_returns_a_download_link(): void
    {
        $response = $this->postJson('/api/book-leads', $this->reader())->assertCreated()->assertJsonPath('success', true);

        $this->assertDatabaseHas('leads', [
            'id' => $response->json('data.id'), 'phone' => '+8801712345678', 'lead_type' => 'Book Download',
            'property_title' => 'Book: The Hidden Business of Land', 'buyer_category' => 'Wants to buy land/flat',
            'utm_campaign' => 'land-book', 'status' => 'New',
        ]);
        $this->assertNotNull(Lead::first()->contact_consented_at);
        $this->assertStringStartsWith('/api/book/download?', $response->json('data.download_url'));
    }

    public function test_retry_returns_the_same_lead(): void
    {
        $payload = $this->reader();
        $first = $this->postJson('/api/book-leads', $payload)->assertCreated();
        $this->postJson('/api/book-leads', $payload)->assertOk()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertDatabaseCount('leads', 1);
    }

    public function test_rejects_a_missing_answer_and_a_bad_phone(): void
    {
        $this->postJson('/api/book-leads', $this->reader(['reason' => null]))->assertUnprocessable()->assertJsonValidationErrors('reason');
        $this->postJson('/api/book-leads', $this->reader(['reason' => 'other']))->assertUnprocessable()->assertJsonValidationErrors('reason');
        $this->postJson('/api/book-leads', $this->reader(['phone' => '12345']))->assertUnprocessable()->assertJsonValidationErrors('phone');
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_the_link_opens_the_pdf_and_a_tampered_link_does_not(): void
    {
        $url = $this->postJson('/api/book-leads', $this->reader())->json('data.download_url');

        $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get($url.'&save=1')->assertOk()->assertHeader('Content-Disposition', 'attachment; filename="The-Hidden-Business-of-Land.pdf"');
        $this->getJson(preg_replace('/lead=\d+/', 'lead=999', $url))->assertForbidden();
        $this->getJson('/api/book/download?lead=1&expires='.(time() - 10).'&sig=x')->assertForbidden();
    }
}
