<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ApiAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_change_properties_users_or_settings(): void
    {
        $property = Property::create(['title' => 'Plot', 'address' => 'Road 1', 'area_name' => 'Gulshan', 'price' => 1]);

        $this->postJson('/api/properties', ['title' => 'Spam'])->assertUnauthorized();
        $this->putJson('/api/properties/'.$property->id, ['title' => 'Changed'])->assertUnauthorized();
        $this->deleteJson('/api/properties/'.$property->id)->assertUnauthorized();
        $this->postJson('/api/users', ['email' => 'x@y.z'])->assertUnauthorized();
        $this->postJson('/api/settings', ['contact_phone' => '1'])->assertUnauthorized();
        $this->getJson('/api/users')->assertUnauthorized();
        $this->assertSame('Plot', $property->fresh()->title);
    }

    public function test_owners_and_buyers_are_not_staff(): void
    {
        $this->signInAs('owner');
        $this->postJson('/api/properties', ['title' => 'Spam'])->assertForbidden();
        $this->getJson('/api/admin/listing-requests')->assertForbidden();
    }

    public function test_hard_coded_default_passwords_no_longer_work(): void
    {
        User::create(['name' => 'Admin', 'email' => 'admin@gbrel.com', 'password' => Hash::make('a-real-password-123'), 'role' => 'admin']);

        $this->postJson('/api/auth/login', ['email' => 'admin@gbrel.com', 'password' => 'admin123'])->assertUnauthorized();
        $this->postJson('/api/auth/login', ['email' => 'admin', 'password' => 'password'])->assertUnauthorized();
        $this->postJson('/api/auth/login', ['email' => 'admin@gbrel.com', 'password' => 'a-real-password-123'])->assertOk()->assertJsonPath('user.role', 'admin');
    }

    public function test_hidden_listings_are_not_public(): void
    {
        $draft = Property::create(['title' => 'Draft plot', 'address' => 'Road 1', 'area_name' => 'Gulshan', 'price' => 1, 'status' => 'Draft']);
        Property::create(['title' => 'Live plot', 'address' => 'Road 2', 'area_name' => 'Gulshan', 'price' => 1, 'status' => 'Active']);

        $this->getJson('/api/properties')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Live plot');
        $this->getJson('/api/properties/'.$draft->id)->assertNotFound();

        Role::create(['name' => 'Property manager', 'slug' => 'property_manager', 'permissions' => ['properties.view']]);
        $this->signInAs('property_manager');
        $this->getJson('/api/properties')->assertJsonCount(2, 'data');
    }

    public function test_storage_route_blocks_path_traversal(): void
    {
        $this->get('/api/storage/../../.env')->assertNotFound();
    }
}
