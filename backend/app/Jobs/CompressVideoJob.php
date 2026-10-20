<?php

namespace App\Jobs;

use App\Services\VideoCompressorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class CompressVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * Create a new job instance.
     *
     * @param  string  $path  Relative storage path of the uploaded video
     * @param  int|null  $mediaId  Optional Media model ID
     */
    public function __construct(
        public string $path,
        public ?int $mediaId = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(VideoCompressorService $compressor): void
    {
        try {
            $result = $compressor->compress($this->path, $this->mediaId);

            if (! empty($result['skipped'])) {
                Log::info("CompressVideoJob: Skipped for {$this->path} ({$result['error']})");
            } elseif (! empty($result['success'])) {
                Log::info("CompressVideoJob: Finished for {$this->path}. Saved {$result['saved_bytes']} bytes.");
            }
        } catch (Throwable $e) {
            Log::error("CompressVideoJob failed for {$this->path}: ".$e->getMessage());
        }
    }
}
