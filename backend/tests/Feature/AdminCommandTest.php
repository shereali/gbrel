<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_new_admin_user_via_command(): void
    {
        $this->artisan('admin:create', [
            'email' => 'customadmin@gbrel.com',
            '--password' => 'SecurePass1234!',
            '--name' => 'Custom Administrator',
        ])->assertSuccessful();

        $user = User::where('email', 'customadmin@gbrel.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Custom Administrator', $user->name);
        $this->assertSame('admin', $user->role);
        $this->assertSame('Active', $user->status);
        $this->assertTrue(Hash::check('SecurePass1234!', $user->password));
    }

    public function test_can_update_existing_admin_password_and_role(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@gbrel.com',
            'password' => Hash::make('oldpassword'),
            'role' => 'buyer',
        ]);

        $this->artisan('admin:create', [
            'email' => 'existing@gbrel.com',
            '--password' => 'NewSecretPassword99!',
            '--force' => true,
        ])->assertSuccessful();

        $user->refresh();
        $this->assertSame('admin', $user->role);
        $this->assertTrue(Hash::check('NewSecretPassword99!', $user->password));
    }

    public function test_can_reset_password_via_reset_alias(): void
    {
        $user = User::factory()->create([
            'email' => 'boss@gbrel.com',
            'password' => Hash::make('oldpassword'),
            'role' => 'admin',
        ]);

        $this->artisan('admin:reset-password', [
            'email' => 'boss@gbrel.com',
            '--password' => 'BrandNewPass2026!',
        ])->assertSuccessful();

        $user->refresh();
        $this->assertTrue(Hash::check('BrandNewPass2026!', $user->password));
    }

    public function test_generates_random_password_when_none_provided(): void
    {
        $this->artisan('admin:create', [
            'email' => 'randomadmin@gbrel.com',
            '--random' => true,
        ])->assertSuccessful();

        $user = User::where('email', 'randomadmin@gbrel.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('admin', $user->role);
        $this->assertNotEmpty($user->password);
    }
}
