<?php

namespace Tests\Feature;

use App\Models\BookOrder;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class BookDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.easypay.api_key' => 'ep_test', 'services.book.price' => 500, 'app.url' => 'https://gbrel.com']);
    }

    private function buyer(array $overrides = []): array
    {
        return [
            'name' => 'Test Buyer', 'phone' => '০১৭১২৩৪৫৬৭৮', 'reason' => 'buy',
            'contact_consent' => true, 'request_id' => (string) Str::uuid(),
            'utm_source' => 'facebook', 'utm_campaign' => 'land-book', 'utm_content' => 'video-hook-1',
            ...$overrides,
        ];
    }

    private function fakeEasyPay(string $paymentStatus = 'pending', float $amount = 500): void
    {
        Http::fake([
            '*/create-payment' => Http::response([
                'status' => 'success', 'transaction_id' => 'EP-TXN-123', 'amount' => 500, 'fee' => 0,
                'currency' => 'BDT', 'checkout_url' => 'https://www.easypaybd.online/checkout/EP-TXN-123',
            ]),
            '*/verify-payment' => Http::response([
                'status' => 'success', 'payment_status' => $paymentStatus, 'trx_id' => 'BK123ABC',
                'amount' => $amount, 'currency' => 'BDT', 'sender_number' => '01712345678', 'payment_method' => 'bkash',
            ]),
        ]);
    }

    public function test_an_order_saves_the_lead_and_returns_the_easypay_checkout(): void
    {
        $this->fakeEasyPay();

        $response = $this->postJson('/api/book-orders', $this->buyer())->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.checkout_url', 'https://www.easypaybd.online/checkout/EP-TXN-123')
            ->assertJsonPath('data.download_url', null);

        $this->assertDatabaseHas('leads', ['phone' => '+8801712345678', 'lead_type' => 'Book Order', 'buyer_category' => 'Wants to buy land/flat']);
        $this->assertDatabaseHas('book_orders', ['public_id' => $response->json('data.order'), 'amount' => 500, 'gateway_txn_id' => 'EP-TXN-123']);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/create-payment')
            && $request->hasHeader('X-API-Key', 'ep_test')
            && $request['amount'] == 500
            && $request['customer_phone'] === '01712345678'
            && $request['callback_url'] === 'https://gbrel.com/book-thanks?order='.$response->json('data.order')
            && $request['webhook_url'] === 'https://gbrel.com/api/book-orders/webhook');
    }

    public function test_a_retry_reuses_the_same_order(): void
    {
        $this->fakeEasyPay();
        $payload = $this->buyer();

        $first = $this->postJson('/api/book-orders', $payload)->assertCreated();
        $this->postJson('/api/book-orders', $payload)->assertOk()->assertJsonPath('data.order', $first->json('data.order'));
        $this->assertDatabaseCount('book_orders', 1);
        $this->assertDatabaseCount('leads', 1);
    }

    public function test_rejects_a_bad_phone_and_refuses_without_an_api_key(): void
    {
        $this->fakeEasyPay();
        $this->postJson('/api/book-orders', $this->buyer(['phone' => '12345']))->assertUnprocessable()->assertJsonValidationErrors('phone');

        config(['services.easypay.api_key' => '']);
        $this->postJson('/api/book-orders', $this->buyer())->assertStatus(503);
        $this->assertDatabaseCount('book_orders', 0);
    }

    public function test_a_pending_order_has_no_download_link(): void
    {
        $this->fakeEasyPay('pending');
        $order = $this->postJson('/api/book-orders', $this->buyer())->json('data.order');

        $this->getJson('/api/book-orders/'.$order)->assertOk()->assertJsonPath('data.status', 'pending')->assertJsonPath('data.download_url', null);
    }

    public function test_a_verified_payment_unlocks_the_pdf(): void
    {
        $this->fakeEasyPay('completed');
        $order = $this->postJson('/api/book-orders', $this->buyer())->json('data.order');

        $url = $this->getJson('/api/book-orders/'.$order)->assertOk()
            ->assertJsonPath('data.status', 'paid')->assertJsonPath('data.trx_id', 'BK123ABC')
            ->json('data.download_url');

        $this->assertStringStartsWith('/api/book/download?', $url);
        $this->assertSame('Book Purchase', Lead::first()->lead_type);
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get($url.'&save=1')->assertOk()->assertHeader('Content-Disposition', 'attachment; filename="The-Hidden-Business-of-Land.pdf"');
        $this->getJson(preg_replace('/order=\d+/', 'order=999', $url))->assertForbidden();
    }

    public function test_the_webhook_only_trusts_easypays_verify_answer(): void
    {
        $verify = fn (string $status) => ['status' => 'success', 'payment_status' => $status, 'trx_id' => 'BK1', 'amount' => 500];
        Http::fake([
            '*/create-payment' => Http::response(['transaction_id' => 'EP-TXN-123', 'checkout_url' => 'https://x.test/c']),
            '*/verify-payment' => Http::sequence()->push($verify('pending'))->push($verify('completed')),
        ]);
        $order = $this->postJson('/api/book-orders', $this->buyer())->json('data.order');

        // A forged "completed" notification: verify-payment still says pending, so nothing unlocks.
        $this->postJson('/api/book-orders/webhook', ['transaction_id' => 'EP-TXN-123', 'status' => 'completed'])->assertOk();
        $this->assertSame('pending', BookOrder::first()->status);

        $this->postJson('/api/book-orders/webhook', ['transaction_id' => 'EP-TXN-123', 'status' => 'completed'])->assertOk();
        $this->assertSame('paid', BookOrder::where('public_id', $order)->first()->status);
    }

    public function test_an_underpayment_does_not_unlock_the_book(): void
    {
        $this->fakeEasyPay('completed', 50);
        $order = $this->postJson('/api/book-orders', $this->buyer())->json('data.order');

        $this->getJson('/api/book-orders/'.$order)->assertJsonPath('data.status', 'underpaid')->assertJsonPath('data.download_url', null);
    }

    public function test_the_free_route_is_retired_and_unpaid_links_are_refused(): void
    {
        $this->postJson('/api/book-leads', $this->buyer())->assertStatus(410);
        $this->getJson('/api/book/download?order=1&expires='.(time() - 10).'&sig=x')->assertForbidden();
    }
}
