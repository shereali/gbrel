<?php

namespace Tests\Feature;

use App\Jobs\CompressVideoJob;
use App\Models\Media;
use App\Services\VideoCompressorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class VideoCompressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploading_video_in_media_library_dispatches_compress_job(): void
    {
        Queue::fake();
        Storage::fake('public');
        $this->signInAs('admin');

        $video = UploadedFile::fake()->create('tour.mp4', 10240, 'video/mp4');

        $response = $this->post('/api/admin/media', ['file' => $video], ['Accept' => 'application/json']);
        $response->assertCreated();

        Queue::assertPushed(CompressVideoJob::class, function ($job) {
            return str_contains($job->path, '.mp4') && $job->mediaId !== null;
        });
    }

    public function test_uploading_video_in_general_upload_endpoint_dispatches_compress_job(): void
    {
        Queue::fake();
        Storage::fake('public');
        $this->signInAs('admin');

        $video = UploadedFile::fake()->create('property_walkthrough.mp4', 20480, 'video/mp4');

        $response = $this->post('/api/upload', ['video' => $video], ['Accept' => 'application/json']);
        $response->assertOk()->assertJsonPath('success', true);

        Queue::assertPushed(CompressVideoJob::class);
    }

    public function test_service_handles_missing_ffmpeg_gracefully_without_error(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/sample.mp4', 'dummy video content');

        // Mock Process to simulate ffmpeg not being installed
        Process::fake([
            '* -version' => Process::result('', 'ffmpeg: command not found', 127),
        ]);

        $service = new VideoCompressorService();
        $this->assertFalse($service->isAvailable());

        $res = $service->compress('media/sample.mp4');
        $this->assertFalse($res['success']);
        $this->assertTrue($res['skipped'] ?? false);
        $this->assertStringContainsString('FFmpeg binary not found', $res['error']);

        // File should still exist unchanged
        Storage::disk('public')->assertExists('media/sample.mp4');
    }

    public function test_service_executes_ffmpeg_compression_when_available(): void
    {
        Storage::fake('public');
        $originalContent = str_repeat('X', 5000);
        Storage::disk('public')->put('media/test.mp4', $originalContent);

        $media = Media::create([
            'type' => 'video',
            'source' => 'upload',
            'url' => '/storage/media/test.mp4',
            'path' => 'media/test.mp4',
            'size' => 5000,
        ]);

        Process::fake([
            '* -version' => Process::result('ffmpeg version 6.1.1', exitCode: 0),
            '* -movflags +faststart *' => function ($process) {
                // In command args or string, find the output temp path and create a smaller file
                $command = is_array($process->command) ? implode(' ', $process->command) : (string) $process->command;
                preg_match('/(\S+\.compressed_[^\s]+(?:\.mp4)?)/', $command, $matches);
                if (! empty($matches[1])) {
                    file_put_contents($matches[1], str_repeat('Y', 2000));
                }

                return Process::result('Conversion completed', exitCode: 0);
            },
            '* -vframes 1 *' => function ($process) {
                $command = is_array($process->command) ? implode(' ', $process->command) : (string) $process->command;
                preg_match('/(\S+_poster\.jpg)/', $command, $matches);
                if (! empty($matches[1])) {
                    file_put_contents($matches[1], 'poster image data');
                }

                return Process::result('Frame captured', exitCode: 0);
            },
        ]);

        $service = new VideoCompressorService();
        $this->assertTrue($service->isAvailable());

        $result = $service->compress('media/test.mp4', $media->id);
        $this->assertTrue($result['success']);
        $this->assertLessThan(5000, $result['compressed_size']);

        $media->refresh();
        $this->assertEquals($result['compressed_size'], $media->size);
        $this->assertEquals('video/mp4', $media->mime_type);
        $this->assertNotNull($media->thumbnail_url);
    }

    public function test_media_status_endpoint_returns_compression_info(): void
    {
        $this->signInAs('admin');

        $media = Media::create([
            'type' => 'video',
            'source' => 'upload',
            'url' => '/storage/media/test_status.mp4',
            'path' => 'media/test_status.mp4',
            'size' => 25000000,
            'original_size' => 100000000,
            'saved_bytes' => 75000000,
            'compression_status' => 'completed',
        ]);

        $res = $this->getJson("/api/admin/media/{$media->id}/status");
        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.compression_status', 'completed')
            ->assertJsonPath('data.original_size', 100000000)
            ->assertJsonPath('data.size', 25000000)
            ->assertJsonPath('data.saved_percent', 75);
    }
}
