<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FFmpeg Binary and Video Compression Configuration
    |--------------------------------------------------------------------------
    |
    | When videos are uploaded to GBREL (e.g. up to 200MB property walkthroughs),
    | this worker compresses them to crisp 1080p web-ready MP4s (H.264 + AAC)
    | with faststart enabled for instant streaming without buffering.
    |
    */

    'ffmpeg_path' => env('FFMPEG_PATH', 'ffmpeg'),
    'ffprobe_path' => env('FFPROBE_PATH', 'ffprobe'),

    'video' => [
        'enabled' => (bool) env('VIDEO_COMPRESSION_ENABLED', true),
        // Constant Rate Factor (CRF): 23-26 is visually lossless for web while saving 60-85% size
        'crf' => (int) env('VIDEO_COMPRESSION_CRF', 24),
        // Max video width (1080p). Videos narrower than 1920px are kept at native width.
        'max_width' => (int) env('VIDEO_MAX_WIDTH', 1920),
        'preset' => env('VIDEO_COMPRESSION_PRESET', 'fast'),
        'audio_bitrate' => env('VIDEO_AUDIO_BITRATE', '128k'),
        // Auto-generate video poster thumbnail if missing
        'generate_poster' => (bool) env('VIDEO_GENERATE_POSTER', true),
        // Max execution time per video in seconds
        'timeout' => (int) env('VIDEO_COMPRESSION_TIMEOUT', 600),
    ],

];
