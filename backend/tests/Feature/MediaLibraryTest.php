<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_buyers_cannot_use_the_library(): void
    {
        $this->getJson('/api/admin/media')->assertUnauthorized();

        $this->signInAs('buyer');
        $this->getJson('/api/admin/media')->assertForbidden();
    }

    public function test_staff_upload_photos_and_videos_and_add_video_links(): void
    {
        Storage::fake('public');
        $this->signInAs('admin');

        $photo = $this->post('/api/admin/media', ['file' => UploadedFile::fake()->image('front.jpg', 1200, 800)], ['Accept' => 'application/json']);
        $photo->assertCreated()->assertJsonPath('data.type', 'image')->assertJsonPath('data.title', 'front');
        Storage::disk('public')->assertExists(Media::first()->path);

        $video = $this->post('/api/admin/media', ['file' => UploadedFile::fake()->create('walkthrough.mp4', 2048, 'video/mp4')], ['Accept' => 'application/json']);
        $video->assertCreated()->assertJsonPath('data.type', 'video');

        $this->postJson('/api/admin/media/link', ['url' => 'https://youtu.be/dQw4w9WgXcQ'])
            ->assertCreated()
            ->assertJsonPath('data.source', 'youtube')
            ->assertJsonPath('data.thumbnail_url', 'https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg');

        $this->postJson('/api/admin/media/link', ['url' => 'javascript:alert(1)'])->assertUnprocessable();
        $this->postJson('/api/admin/media/link', ['url' => 'https://example.com/video'])->assertUnprocessable();

        $this->getJson('/api/admin/media?type=video')->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('counts.all', 3);
    }

    public function test_media_used_by_a_listing_cannot_be_deleted(): void
    {
        Storage::fake('public');
        $this->signInAs('admin');
        $id = $this->post('/api/admin/media', ['file' => UploadedFile::fake()->image('plot.jpg')], ['Accept' => 'application/json'])->json('data.id');
        $url = Media::find($id)->url;
        Property::create(['title' => 'Plot', 'slug' => 'plot', 'address' => 'Gulshan', 'city' => 'Dhaka', 'area_name' => 'Gulshan', 'price' => 1, 'images' => [$url]]);

        $this->deleteJson("/api/admin/media/{$id}")->assertStatus(409)->assertJsonPath('used_in.0.as', 'photo');

        Property::query()->update(['images' => json_encode([])]);
        $this->deleteJson("/api/admin/media/{$id}")->assertOk();
        $this->assertDatabaseMissing('media', ['id' => $id]);
    }

    public function test_property_video_fields_are_saved_safely_and_public(): void
    {
        $this->signInAs('admin');
        $id = $this->postJson('/api/properties', [
            'title' => 'Video listing', 'price' => 100, 'status' => 'Active', 'images' => ['/img/properties/a.jpg'],
            'videoUrl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'coverMedia' => 'video', 'videoPoster' => '/img/properties/a.jpg',
        ])->assertSuccessful()->json('data.id');

        $property = Property::find($id);
        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $property->video_url);
        $this->assertSame('video', $property->cover_media);

        $this->putJson("/api/properties/{$id}", ['videoUrl' => 'javascript:alert(1)', 'coverMedia' => 'banner', 'videoPoster' => 'data:text/html,x'])->assertSuccessful();
        $property->refresh();
        $this->assertNull($property->video_url);
        $this->assertNull($property->video_poster);
        $this->assertSame('image', $property->cover_media);
    }
}
