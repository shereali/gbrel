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
class UserPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        return User::create(['name' => 'Rahela Akter', 'phone' => '+8801711223344', 'email' => 'rahela@example.test', 'password' => 'old-password-1', 'role' => 'owner']);
    }

    private function change(User $user, string $password = 'new-password-9', ?string $confirmation = null)
    {
        return $this->postJson("/api/users/{$user->id}/password", ['password' => $password, 'password_confirmation' => $confirmation ?? $password]);
    }

    public function test_admin_sets_a_new_password_and_the_user_can_sign_in_with_it(): void
    {
        $member = $this->member();
        $this->signInAs('admin');

        $this->change($member)->assertOk()->assertJsonPath('success', true);

        $this->flushHeaders();
        $this->postJson('/api/auth/login', ['email' => 'rahela@example.test', 'password' => 'new-password-9'])->assertOk();
        $this->postJson('/api/auth/login', ['email' => 'rahela@example.test', 'password' => 'old-password-1'])->assertUnauthorized();
    }

    public function test_short_or_mismatched_passwords_change_nothing(): void
    {
        $member = $this->member();
        $this->signInAs('admin');

        $this->change($member, 'short')->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->change($member, 'new-password-9', 'different-password')->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->postJson("/api/users/{$member->id}/password", [])->assertUnprocessable();
        $this->assertTrue(Hash::check('old-password-1', $member->fresh()->password));
    }

    public function test_only_staff_who_manage_users_can_do_it_and_only_admins_can_change_admins(): void
    {
        $member = $this->member();
        $admin = User::create(['name' => 'Boss', 'email' => 'boss@example.test', 'password' => 'secret-password', 'role' => 'admin']);

        $this->change($member)->assertUnauthorized();
        $this->signInAs('buyer');
        $this->change($member)->assertForbidden();

        $this->signInAs('property_manager', ['custom_permissions' => ['users.manage']]);
        $this->change($admin)->assertForbidden();
        $this->change($member)->assertOk();
        $this->assertTrue(Hash::check('secret-password', $admin->fresh()->password));
    }

    public function test_admin_can_delete_user_and_cannot_delete_self_or_root(): void
    {
        $admin = $this->signInAs('admin');
        $member = $this->member();

        // Cannot delete primary root admin
        $rootEmail = env('ADMIN_EMAIL', 'admin@gbrel.com');
        $root = User::create(['name' => 'Root', 'email' => $rootEmail, 'password' => 'secret', 'role' => 'admin']);
        $this->deleteJson("/api/users/{$root->id}")->assertForbidden();

        // Cannot delete own account
        $this->deleteJson("/api/users/{$admin->id}")->assertForbidden();

        // Can delete another user account
        $this->deleteJson("/api/users/{$member->id}")->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseMissing('users', ['id' => $member->id]);
    }
}
