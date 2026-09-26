<?php

namespace Tests\Feature;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class PropertyBuyerDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_read_update_and_clear_public_buyer_details(): void
    {
        $this->signInAs('admin');
        $response = $this->postJson('/api/properties', [
            'title' => 'Building with land', 'address' => 'Test road', 'areaName' => 'Test area',
            'price' => 140000000, 'landSize' => 31, 'landUnit' => 'Katha', 'totalFloors' => 2,
            'buyerDetails' => ['priceBasis' => 'Per land unit', 'ownerCount' => 3, 'depositPercent' => 30, 'buyerCommission' => 0, 'bankLoan' => 'None declared', 'taxPaidThrough' => '1432 Bangla', 'sourceDate' => '2026-09-15'],
        ])->assertCreated();
        $id = $response->json('data.id');
        $this->getJson('/api/properties/'.$id)->assertOk()
            ->assertJsonPath('data.buyer_details.priceBasis', 'Per land unit')
            ->assertJsonPath('data.buyer_details.buyerCommission', 0)
            ->assertJsonPath('data.buyer_details.ownerCount', 3)
            ->assertJsonPath('data.total_floors', 2)
            ->assertJsonPath('data.images', []);
        $this->putJson('/api/properties/'.$id, ['buyerDetails' => ['documentSummary' => 'Original deed available', 'depositPercent' => '']])->assertOk();
        $this->getJson('/api/properties/'.$id)->assertJsonPath('data.buyer_details', ['documentSummary' => 'Original deed available']);
        $this->putJson('/api/properties/'.$id, ['title' => 'Updated title'])->assertOk();
        $this->getJson('/api/properties/'.$id)->assertJsonPath('data.buyer_details.documentSummary', 'Original deed available');
        $this->putJson('/api/properties/'.$id, ['buyer_details' => []])->assertOk();
        $this->assertSame([], Property::findOrFail($id)->buyer_details);
    }

    public function test_invalid_details_and_unrecognized_private_fields_are_rejected(): void
    {
        $this->signInAs('admin');
        $this->postJson('/api/properties', ['buyerDetails' => [
            'depositPercent' => 101, 'ownerCount' => -1, 'bankLoan' => 'verified',
            'sourceDate' => 'not a date', 'documentSummary' => str_repeat('x', 2001),
        ]])->assertUnprocessable()->assertJsonValidationErrors([
            'buyer_details.depositPercent', 'buyer_details.ownerCount', 'buyer_details.bankLoan',
            'buyer_details.sourceDate', 'buyer_details.documentSummary',
        ]);
        $this->postJson('/api/properties', ['buyerDetails' => ['ownerNid' => 'private-data']])
            ->assertUnprocessable()->assertJsonValidationErrors('buyer_details');
        $this->postJson('/api/properties', ['buyerDetails' => 'bad input'])
            ->assertUnprocessable()->assertJsonValidationErrors('buyer_details');
        $this->assertDatabaseCount('properties', 0);
    }
}
