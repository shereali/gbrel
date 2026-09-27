<?php

namespace App\Support;

/**
 * Recognises the video links staff may attach to a listing. Anything else is rejected, so the public page
 * only ever embeds players from these providers or plays a file from our own storage.
 */
class VideoLink
{
    /**
     * @return array{source: string, id: ?string, thumbnail: ?string}|null
     */
    public static function parse(?string $url): ?array
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if (preg_match('~^/storage/media/(?:[\w-]+/)*[\w-]+\.(mp4|webm|mov|m4v)\z~i', $url)) {
            return ['source' => 'upload', 'id' => null, 'thumbnail' => null];
        }
        if (! preg_match('~^https://~i', $url)) {
            return null;
        }
        if (preg_match('~^https://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/)|youtu\.be/)([\w-]{11})~i', $url, $m)) {
            return ['source' => 'youtube', 'id' => $m[1], 'thumbnail' => "https://i.ytimg.com/vi/{$m[1]}/hqdefault.jpg"];
        }
        if (preg_match('~^https://(?:www\.)?vimeo\.com/(\d{6,12})~i', $url, $m)) {
            return ['source' => 'vimeo', 'id' => $m[1], 'thumbnail' => null];
        }
        if (preg_match('~^https://(?:www\.|m\.|web\.)?(?:facebook\.com|fb\.watch)/~i', $url)) {
            return ['source' => 'facebook', 'id' => null, 'thumbnail' => null];
        }

        return null;
    }

    public static function isPoster(?string $url): bool
    {
        $url = trim((string) $url);

        if (str_contains($url, '..')) {
            return false;
        }

        return $url === '' || str_starts_with($url, '/storage/') || str_starts_with($url, '/img/') || (bool) preg_match('~^https://~i', $url);
    }
}
