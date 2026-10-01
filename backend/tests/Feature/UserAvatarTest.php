<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class UserAvatarTest extends TestCase
{
    use RefreshDatabase;

    private function member(array $attributes = []): User
    {
        return User::create(array_merge(['name' => 'Team Member', 'email' => 'member@example.test', 'password' => 'secret-password', 'role' => 'agent'], $attributes));
    }

    public function test_admin_uploads_a_photo_and_a_new_one_replaces_the_old_file(): void
    {
        Storage::fake('public');
        $this->signInAs('admin');
        $member = $this->member(['avatar' => 'https://images.unsplash.com/photo-1?q=80']);

        $first = $this->postJson("/api/users/{$member->id}/avatar", ['avatar' => UploadedFile::fake()->image('me.jpg', 300, 300)])->assertOk()->json('data.avatar');
        $this->assertStringStartsWith('/storage/avatars/', $first);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $first));

        $second = $this->postJson("/api/users/{$member->id}/avatar", ['avatar' => UploadedFile::fake()->image('new.png', 300, 300)])->assertOk()->json('data.avatar');

        Storage::disk('public')->assertMissing(str_replace('/storage/', '', $first));
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $second));
        $this->assertSame($second, $member->fresh()->avatar);
        $this->getJson('/api/users')->assertOk()->assertJsonFragment(['avatar' => $second]);
    }

    public function test_only_photos_up_to_five_megabytes_are_accepted(): void
    {
        Storage::fake('public');
        $this->signInAs('admin');
        $member = $this->member();

        $this->postJson("/api/users/{$member->id}/avatar", ['avatar' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf')])->assertUnprocessable()->assertJsonValidationErrors('avatar');
        $this->postJson("/api/users/{$member->id}/avatar", ['avatar' => UploadedFile::fake()->image('huge.jpg')->size(6000)])->assertUnprocessable()->assertJsonValidationErrors('avatar');
        $this->postJson("/api/users/{$member->id}/avatar", [])->assertUnprocessable();
        $this->assertNull($member->fresh()->avatar);
    }

    public function test_removing_a_photo_deletes_our_file_but_never_touches_external_links(): void
    {
        Storage::fake('public');
        $this->signInAs('admin');
        $member = $this->member();
        $url = $this->postJson("/api/users/{$member->id}/avatar", ['avatar' => UploadedFile::fake()->image('me.jpg')])->json('data.avatar');

        $this->deleteJson("/api/users/{$member->id}/avatar")->assertOk();
        Storage::disk('public')->assertMissing(str_replace('/storage/', '', $url));
        $this->assertNull($member->fresh()->avatar);

        $member->forceFill(['avatar' => 'https://example.test/pic.jpg'])->save();
        $this->deleteJson("/api/users/{$member->id}/avatar")->assertOk();
        $this->assertNull($member->fresh()->avatar);
    }

    public function test_only_staff_who_manage_users_can_change_photos(): void
    {
        Storage::fake('public');
        $member = $this->member();
        $photo = ['avatar' => UploadedFile::fake()->image('me.jpg')];

        $this->postJson("/api/users/{$member->id}/avatar", $photo)->assertUnauthorized();
        $this->signInAs('buyer');
        $this->postJson("/api/users/{$member->id}/avatar", $photo)->assertForbidden();
        $this->deleteJson("/api/users/{$member->id}/avatar")->assertForbidden();
        $this->assertNull($member->fresh()->avatar);
    }
}
