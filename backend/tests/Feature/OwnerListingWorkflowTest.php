<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Role;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class OwnerListingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Legal', 'slug' => 'legal_compliance', 'permissions' => ['properties.view', 'properties.edit', 'listings.review']]);
        Role::create(['name' => 'Property manager', 'slug' => 'property_manager', 'permissions' => ['properties.view', 'properties.create', 'properties.edit', 'listings.review']]);
    }

    /**
     * @return array<string, mixed>
     */
    private function completeListing(): array
    {
        return [
            'title' => 'গুলশান-২ এ ১৭ কাঠার প্লট', 'property_type' => 'Plot', 'city' => 'ঢাকা', 'area_name' => 'গুলশান-২',
            'address' => 'রোড ৯২, গুলশান-২', 'land_size' => 17, 'land_unit' => 'Katha',
            'owner_details' => [
                'fullAddress' => 'প্লট ০৬, রোড ৯২, গুলশান-২', 'submitterRole' => 'Owner', 'ownerCount' => 2,
                'allOwnersAgree' => 'Yes', 'ownershipSource' => 'Inheritance', 'possession' => 'Owner',
                'bankLoan' => 'None', 'existingAgreement' => 'None', 'mutationStatus' => 'Done',
                'disputeOrCase' => 'No', 'expectedPrice' => 70000000, 'priceBasis' => 'Per land unit',
            ],
        ];
    }

    public function test_owner_registers_and_draft_stays_private(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'রহিম', 'phone' => '01712 345678', 'password' => 'long-password', 'password_confirmation' => 'long-password',
        ])->assertCreated()->assertJsonPath('user.role', 'owner');
        $this->assertDatabaseHas('users', ['phone' => '+8801712345678', 'role' => 'owner']);

        $this->withHeader('Authorization', 'Bearer '.$response->json('token'));
        $id = $this->postJson('/api/owner/listings', $this->completeListing())->assertCreated()
            ->assertJsonPath('data.review_status', 'draft')->json('data.id');

        $this->flushHeaders();
        $this->getJson('/api/properties')->assertJsonCount(0, 'data');
        $this->getJson('/api/properties/'.$id)->assertNotFound();

        $this->postJson('/api/auth/login', ['email' => '01712345678', 'password' => 'long-password'])->assertOk();
    }

    public function test_owner_cannot_see_or_edit_another_owners_listing(): void
    {
        $this->signInAs('owner');
        $id = $this->postJson('/api/owner/listings', $this->completeListing())->json('data.id');

        $this->signInAs('owner');
        $this->getJson('/api/owner/listings/'.$id)->assertNotFound();
        $this->putJson('/api/owner/listings/'.$id, ['title' => 'Hijack'])->assertNotFound();
    }

    public function test_full_review_flow_from_submission_to_publish(): void
    {
        Storage::fake('local');
        $owner = $this->signInAs('owner');
        $id = $this->postJson('/api/owner/listings', $this->completeListing())->json('data.id');

        $upload = $this->post('/api/owner/listings/'.$id.'/documents', [
            'document_type' => 'deed', 'file' => UploadedFile::fake()->create('deed.pdf', 200, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertCreated();
        $documentId = $upload->json('data.id');
        $upload->assertJsonMissingPath('data.file_path');

        $this->postJson('/api/owner/listings/'.$id.'/submit', ['accept_terms' => true, 'terms_version' => 'wrong'])->assertUnprocessable();
        $version = SiteSettings::all()['owner_terms_version'];
        $this->postJson('/api/owner/listings/'.$id.'/submit', ['accept_terms' => true, 'terms_version' => $version])
            ->assertOk()->assertJsonPath('data.review_status', 'submitted');
        $this->assertEquals(2, Property::find($id)->owner_agreement['commission_percent']);

        $this->signInAs('legal_compliance');
        $this->getJson('/api/admin/listing-requests')->assertOk()->assertJsonPath('data.0.owner.id', $owner->id);
        $this->get('/api/listing-documents/'.$documentId.'/file')->assertOk();
        $this->patchJson('/api/admin/listing-documents/'.$documentId, ['status' => 'rejected'])->assertUnprocessable();
        $this->patchJson('/api/admin/listing-documents/'.$documentId, ['status' => 'verified'])->assertOk();

        $this->patchJson('/api/admin/listing-requests/'.$id.'/review', ['action' => 'publish'])->assertUnprocessable();
        $this->patchJson('/api/admin/listing-requests/'.$id.'/review', ['action' => 'approve'])->assertOk();
        $this->patchJson('/api/admin/listing-requests/'.$id.'/review', ['action' => 'publish'])->assertOk()->assertJsonPath('data.is_live', true);

        $this->flushHeaders();
        $public = $this->getJson('/api/properties/'.$id)->assertOk();
        $public->assertJsonMissingPath('data.owner_id')->assertJsonMissingPath('data.owner_details');
        $this->get('/api/listing-documents/'.$documentId.'/file')->assertUnauthorized();
    }

    public function test_owner_edits_to_a_live_listing_wait_for_review(): void
    {
        $this->signInAs('owner');
        $id = $this->postJson('/api/owner/listings', $this->completeListing())->json('data.id');
        Property::find($id)->update(['review_status' => 'approved', 'status' => 'Active', 'published_at' => now()]);

        $this->putJson('/api/owner/listings/'.$id, ['title' => 'নতুন শিরোনাম'])->assertOk()->assertJsonPath('data.review_status', 'update_submitted');
        $this->assertSame('গুলশান-২ এ ১৭ কাঠার প্লট', Property::find($id)->title);

        $this->signInAs('property_manager');
        $this->postJson('/api/admin/listing-requests/'.$id.'/pending-changes', ['decision' => 'apply'])->assertOk();
        $this->assertSame('নতুন শিরোনাম', Property::find($id)->title);
    }

    public function test_changes_requested_lets_owner_fix_and_resubmit(): void
    {
        $this->signInAs('owner');
        $id = $this->postJson('/api/owner/listings', $this->completeListing())->json('data.id');
        Property::find($id)->update(['review_status' => 'submitted', 'status' => 'Pending Review']);

        $this->signInAs('legal_compliance');
        $this->patchJson('/api/admin/listing-requests/'.$id.'/review', ['action' => 'request_changes'])->assertUnprocessable();
        $this->patchJson('/api/admin/listing-requests/'.$id.'/review', ['action' => 'request_changes', 'note' => 'খাজনার দাখিলা দিন'])->assertOk();
        $this->assertSame('changes_requested', Property::find($id)->review_status);
    }
}
