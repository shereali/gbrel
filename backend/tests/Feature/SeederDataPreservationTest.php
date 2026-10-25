<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Property;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\AgentSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PropertySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederDataPreservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_seeder_does_not_overwrite_user_saved_settings(): void
    {
        // 1. Initial seed
        $this->seed(SettingSeeder::class);

        // 2. User updates office_address and contact phone
        Setting::setVal('office_address', 'Custom User Address, Banani, Dhaka');
        Setting::setVal('contact_phone', '+8801999999999');

        // 3. Deploy/restart runs SettingSeeder again
        $this->seed(SettingSeeder::class);

        // 4. Verify user-saved data is preserved!
        $this->assertSame('Custom User Address, Banani, Dhaka', Setting::getVal('office_address'));
        $this->assertSame('+8801999999999', Setting::getVal('contact_phone'));
    }

    public function test_agent_seeder_does_not_overwrite_modified_agent_or_resurrect_deleted_agent(): void
    {
        // 1. Initial seed
        $this->seed(AgentSeeder::class);

        // 2. Admin edits an agent's phone and bio
        $agent = Agent::where('email', 'abu.hanif@gbrel.com')->first();
        $this->assertNotNull($agent);
        $agent->update([
            'phone' => '+8801700000000',
            'bio' => 'Custom updated bio by user.',
        ]);

        // 3. Admin deletes another agent
        $toDelete = Agent::where('email', 'siam.talukder@gbrel.com')->first();
        $this->assertNotNull($toDelete);
        $toDelete->delete();

        // 4. Deploy/restart runs AgentSeeder again
        $this->seed(AgentSeeder::class);

        // 5. Verify the updated agent kept changes and deleted agent was NOT resurrected
        $agent->refresh();
        $this->assertSame('+8801700000000', $agent->phone);
        $this->assertSame('Custom updated bio by user.', $agent->bio);
        $this->assertNull(Agent::where('email', 'siam.talukder@gbrel.com')->first());
    }

    public function test_user_seeder_does_not_reset_admin_password(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(UserSeeder::class);

        // Admin updates password in admin panel
        $admin = User::where('email', 'admin@gbrel.com')->first();
        $this->assertNotNull($admin);
        $admin->update([
            'password' => Hash::make('MySuperSecretPassword2026!'),
            'name' => 'Custom Admin Name',
        ]);

        // Deploy/restart runs UserSeeder again
        $this->seed(UserSeeder::class);

        $admin->refresh();
        $this->assertSame('Custom Admin Name', $admin->name);
        $this->assertTrue(Hash::check('MySuperSecretPassword2026!', $admin->password));
        $this->assertFalse(Hash::check('admin123', $admin->password));
    }

    public function test_full_database_seeder_run_preserves_all_user_customizations(): void
    {
        // Initial run
        $this->seed(DatabaseSeeder::class);

        // User customizes settings and profile
        Setting::setVal('office_address', 'Level 5, Gulshan Avenue, Dhaka');
        $admin = User::where('email', 'admin@gbrel.com')->first();
        $admin->update(['name' => 'Lead Executive']);

        // Deploy/restart runs the whole DatabaseSeeder
        $this->seed(DatabaseSeeder::class);

        // Everything should stay preserved
        $this->assertSame('Level 5, Gulshan Avenue, Dhaka', Setting::getVal('office_address'));
        $admin->refresh();
        $this->assertSame('Lead Executive', $admin->name);
    }
}
