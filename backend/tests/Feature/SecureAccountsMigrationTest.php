<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class SecureAccountsMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function runMigration(): void
    {
        $migration = require database_path('migrations/2026_09_27_000011_secure_accounts_and_fix_launch_listings.php');
        $migration->up();
    }

    private function setAdminPassword(string $value): void
    {
        putenv('ADMIN_PASSWORD='.$value);
        $_ENV['ADMIN_PASSWORD'] = $value;
        $_SERVER['ADMIN_PASSWORD'] = $value;
    }

    public function test_public_default_passwords_are_retired(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@gbrel.com', 'password' => Hash::make('admin123'), 'role' => 'admin']);
        $demo = User::create(['name' => 'Manager', 'email' => 'manager@gbrel.com', 'password' => Hash::make('manager123'), 'role' => 'property_manager']);
        $changed = User::create(['name' => 'Legal', 'email' => 'legal@gbrel.com', 'password' => Hash::make('a-real-password'), 'role' => 'legal_compliance']);
        $this->setAdminPassword('New-Strong-Pass-2026');

        $this->runMigration();

        $this->assertTrue(Hash::check('New-Strong-Pass-2026', $admin->fresh()->password));
        $this->assertSame('Suspended', $demo->fresh()->status);
        $this->assertNotSame('Suspended', $changed->fresh()->status);
    }

    public function test_an_admin_whose_password_was_already_changed_is_left_alone(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@gbrel.com', 'password' => Hash::make('owner-chosen-password'), 'role' => 'admin']);
        $this->setAdminPassword('Something-Else-2026');

        $this->runMigration();

        $this->assertTrue(Hash::check('owner-chosen-password', $admin->fresh()->password));
    }
}
