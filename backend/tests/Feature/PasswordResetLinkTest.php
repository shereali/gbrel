<?php

namespace Tests\Feature;

use App\Models\PasswordResetLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class PasswordResetLinkTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::create(['name' => 'Rahela Akter', 'phone' => '+8801711223344', 'email' => null, 'password' => 'old-password-1', 'role' => 'owner']);
    }

    private function linkFor(User $user): string
    {
        return $this->postJson("/api/users/{$user->id}/password-reset")->assertCreated()->json('data.token');
    }

    private function reset(string $token, string $password = 'new-password-9')
    {
        return $this->postJson('/api/auth/reset-password', ['token' => $token, 'password' => $password, 'password_confirmation' => $password]);
    }

    public function test_staff_creates_a_link_and_the_user_can_then_sign_in_with_the_new_password_by_phone(): void
    {
        $owner = $this->owner();
        $this->signInAs('admin');
        $token = $this->linkFor($owner);

        $this->assertDatabaseMissing('password_reset_links', ['token_hash' => $token]);
        $this->postJson('/api/auth/reset-password/check', ['token' => $token])->assertOk()->assertJsonPath('data.name', 'Rahela Akter');
        $this->reset($token)->assertOk();

        $this->flushHeaders();
        $this->postJson('/api/auth/login', ['email' => '01711223344', 'password' => 'new-password-9'])->assertOk();
        $this->postJson('/api/auth/login', ['email' => '01711223344', 'password' => 'old-password-1'])->assertUnauthorized();
    }

    public function test_a_link_works_only_once(): void
    {
        $owner = $this->owner();
        $this->signInAs('admin');
        $token = $this->linkFor($owner);

        $this->reset($token)->assertOk();
        $this->reset($token, 'another-password-1')->assertUnprocessable();
        $this->postJson('/api/auth/reset-password/check', ['token' => $token])->assertUnprocessable();
        $this->assertTrue(Hash::check('new-password-9', $owner->fresh()->password));
    }

    public function test_a_newer_link_replaces_the_older_one_and_expired_links_stop_working(): void
    {
        $owner = $this->owner();
        $this->signInAs('admin');
        $first = $this->linkFor($owner);
        $second = $this->linkFor($owner);

        $this->reset($first)->assertUnprocessable();

        PasswordResetLink::query()->update(['expires_at' => now()->subMinute()]);
        $this->reset($second)->assertUnprocessable();
        $this->assertTrue(Hash::check('old-password-1', $owner->fresh()->password));
    }

    public function test_weak_or_mismatched_passwords_are_rejected_and_do_not_use_up_the_link(): void
    {
        $owner = $this->owner();
        $this->signInAs('admin');
        $token = $this->linkFor($owner);

        $this->reset($token, 'short')->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->postJson('/api/auth/reset-password', ['token' => $token, 'password' => 'new-password-9', 'password_confirmation' => 'different-9'])->assertUnprocessable();
        $this->reset($token)->assertOk();
    }

    public function test_made_up_tokens_are_rejected(): void
    {
        $this->reset('not-a-real-token-not-a-real-token')->assertUnprocessable();
        $this->reset('x')->assertUnprocessable();
        $this->postJson('/api/auth/reset-password/check', [])->assertUnprocessable();
    }

    public function test_only_staff_who_manage_users_can_create_links_and_only_admins_can_reset_admins(): void
    {
        $owner = $this->owner();
        $admin = User::create(['name' => 'Boss', 'email' => 'boss@example.test', 'password' => 'secret-password', 'role' => 'admin']);

        $this->postJson("/api/users/{$owner->id}/password-reset")->assertUnauthorized();
        $this->signInAs('buyer');
        $this->postJson("/api/users/{$owner->id}/password-reset")->assertForbidden();

        $this->signInAs('property_manager', ['custom_permissions' => ['users.manage']]);
        $this->postJson("/api/users/{$admin->id}/password-reset")->assertForbidden();
        $this->postJson("/api/users/{$owner->id}/password-reset")->assertCreated();
        $this->assertDatabaseCount('password_reset_links', 1);
    }
}
