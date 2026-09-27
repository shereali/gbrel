<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A shared media library (photos, uploaded videos, YouTube/Facebook/Vimeo links) and a video slot on
     * every property with a choice of what buyers see first.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10); // image | video
            $table->string('source', 20); // upload | youtube | facebook | vimeo | link
            $table->string('url', 500);
            $table->string('path', 500)->nullable(); // file on the public disk, for uploads
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('title', 200)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['type', 'created_at']);
            $table->unique('url');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->string('video_url', 500)->nullable()->after('images');
            $table->string('video_poster', 500)->nullable()->after('video_url');
            $table->string('cover_media', 10)->default('image')->after('video_poster');
        });

        // Existing listing photos become library items so staff can reuse them.
        $seen = [];
        foreach (DB::table('properties')->select('images')->get() as $row) {
            foreach ((array) json_decode((string) $row->images, true) as $url) {
                if (! is_string($url) || $url === '' || isset($seen[$url]) || strlen($url) > 500) {
                    continue;
                }
                $seen[$url] = true;
                DB::table('media')->insert([
                    'type' => 'image',
                    'source' => str_starts_with($url, '/storage/') ? 'upload' : 'link',
                    'url' => $url,
                    'path' => str_starts_with($url, '/storage/') ? substr($url, strlen('/storage/')) : null,
                    'title' => pathinfo(parse_url($url, PHP_URL_PATH) ?: $url, PATHINFO_FILENAME),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'video_poster', 'cover_media']);
        });
        Schema::dropIfExists('media');
    }
};
