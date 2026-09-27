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

    public function test_only_real_photos_and_videos_within_the_size_limits_are_accepted(): void
    {
        Storage::fake('public');
        $this->signInAs('admin');
        $upload = fn (UploadedFile $file) => $this->post('/api/admin/media', ['file' => $file], ['Accept' => 'application/json']);

        $upload(UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'))->assertUnprocessable();
        $upload(UploadedFile::fake()->createWithContent('page.html', '<html><script>alert(1)</script></html>'))->assertUnprocessable();
        $upload(UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf'))->assertUnprocessable();
        // A PHP file renamed to .jpg: the server reads the real content type, not the name.
        $disguised = tempnam(sys_get_temp_dir(), 'up');
        file_put_contents($disguised, "<?php echo 'hi';\n");
        $upload(new UploadedFile($disguised, 'photo.jpg', null, null, true))->assertUnprocessable();
        $upload(UploadedFile::fake()->image('huge.jpg')->size(11 * 1024))->assertUnprocessable();

        $this->assertSame(0, Media::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_media_names_and_covers_can_be_updated_with_safe_values_only(): void
    {
        Storage::fake('public');
        $this->signInAs('admin');
        $id = $this->postJson('/api/admin/media/link', ['url' => 'https://youtu.be/dQw4w9WgXcQ'])->json('data.id');

        $this->patchJson("/api/admin/media/{$id}", ['title' => 'Site walkthrough'])->assertOk()->assertJsonPath('data.title', 'Site walkthrough');
        $this->patchJson("/api/admin/media/{$id}", ['thumbnail_url' => 'javascript:alert(1)'])->assertUnprocessable();
        $this->patchJson("/api/admin/media/{$id}", ['thumbnail_url' => '/storage/../../.env'])->assertUnprocessable();
        $this->patchJson("/api/admin/media/{$id}", ['thumbnail_url' => '/storage/media/2026/09/cover.jpg'])->assertOk()->assertJsonPath('data.thumbnail_url', '/storage/media/2026/09/cover.jpg');
    }

    public function test_owner_uploads_stay_out_of_the_staff_library(): void
    {
        Storage::fake('public');
        $this->signInAs('owner');
        $this->post('/api/upload', ['image' => UploadedFile::fake()->image('my-plot.jpg')], ['Accept' => 'application/json'])->assertOk();
        $this->assertSame(0, Media::count());

        $this->signInAs('admin');
        $this->post('/api/upload', ['image' => UploadedFile::fake()->image('office-photo.jpg')], ['Accept' => 'application/json'])->assertOk();
        $this->assertSame(1, Media::count());
    }

    public function test_photos_used_as_a_video_cover_or_in_a_pending_owner_edit_cannot_be_deleted(): void
    {
        Storage::fake('public');
        $this->signInAs('admin');
        $coverId = $this->post('/api/admin/media', ['file' => UploadedFile::fake()->image('cover.jpg')], ['Accept' => 'application/json'])->json('data.id');
        $coverUrl = Media::find($coverId)->url;
        $videoId = $this->postJson('/api/admin/media/link', ['url' => 'https://youtu.be/dQw4w9WgXcQ'])->json('data.id');
        $this->patchJson("/api/admin/media/{$videoId}", ['thumbnail_url' => $coverUrl])->assertOk();

        $this->deleteJson("/api/admin/media/{$coverId}")->assertStatus(409)->assertJsonPath('used_in.0.kind', 'media');

        $this->patchJson("/api/admin/media/{$videoId}", ['thumbnail_url' => null])->assertOk();
        Property::create(['title' => 'Owner plot', 'slug' => 'owner-plot', 'address' => 'Uttara', 'city' => 'Dhaka', 'area_name' => 'Uttara', 'price' => 1, 'owner_pending_changes' => ['images' => [$coverUrl]]]);
        $this->deleteJson("/api/admin/media/{$coverId}")->assertStatus(409)->assertJsonPath('used_in.0.as', "photo in owner's pending edit");

        Property::query()->update(['owner_pending_changes' => null]);
        $this->deleteJson("/api/admin/media/{$coverId}")->assertOk();
    }

    public function test_the_library_is_paged(): void
    {
        $this->signInAs('admin');
        foreach (range(1, 65) as $i) {
            Media::create(['type' => 'image', 'source' => 'upload', 'url' => "/storage/media/2026/09/p{$i}.jpg", 'path' => "media/2026/09/p{$i}.jpg", 'title' => "Photo {$i}"]);
        }

        $this->getJson('/api/admin/media')->assertOk()->assertJsonCount(60, 'data')->assertJsonPath('meta.last_page', 2)->assertJsonPath('meta.total', 65);
        $this->getJson('/api/admin/media?page=2')->assertOk()->assertJsonCount(5, 'data')->assertJsonPath('data.4.title', 'Photo 1');
    }
}
