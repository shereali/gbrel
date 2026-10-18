<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class SecureAccountsSeederTest extends TestCase
{
    use RefreshDatabase;

    private function setAdminPassword(string $value): void
    {
        putenv('ADMIN_PASSWORD='.$value);
        $_ENV['ADMIN_PASSWORD'] = $value;
        $_SERVER['ADMIN_PASSWORD'] = $value;
    }

    public function test_user_seeder_creates_admin_and_staff_accounts(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->setAdminPassword('New-Strong-Pass-2026');

        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@gbrel.com')->first();
        $this->assertNotNull($admin);
        $this->assertTrue(Hash::check('New-Strong-Pass-2026', $admin->password));
        $this->assertSame('Active', $admin->status);

        $manager = User::where('email', 'manager@gbrel.com')->first();
        $this->assertNotNull($manager);
        $this->assertSame('Active', $manager->status);
    }
}
