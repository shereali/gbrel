<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Property;
use App\Support\VideoLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Staff media library: photos and videos that can be attached to any property.
 */
class MediaLibraryController extends Controller
{
    public const IMAGE_MAX_KB = 10240;

    public const VIDEO_MAX_KB = 102400;

    public const PER_PAGE = 60;

    /** Uploads stop while less than this much disk is free, so a burst of videos cannot fill the server. */
    public const MIN_FREE_BYTES = 2 * 1024 * 1024 * 1024;

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['nullable', 'in:image,video'],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Media::query()->latest('id');
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.addcslashes((string) $request->input('q'), '%_\\').'%');
        }

        $page = $query->paginate(self::PER_PAGE);
        $usage = $this->usageFor($page->getCollection());

        return response()->json([
            'success' => true,
            'data' => $page->getCollection()->map(fn (Media $media) => $this->present($media, $usage))->values(),
            'meta' => ['page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
            'counts' => [
                'all' => Media::count(),
                'image' => Media::where('type', 'image')->count(),
                'video' => Media::where('type', 'video')->count(),
            ],
            'limits' => ['image_mb' => self::IMAGE_MAX_KB / 1024, 'video_mb' => self::VIDEO_MAX_KB / 1024],
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime', 'max:'.self::VIDEO_MAX_KB],
            'title' => ['nullable', 'string', 'max:200'],
        ]);

        $file = $request->file('file');
        $free = @disk_free_space(Storage::disk('public')->path(''));
        if ($free !== false && $free - $file->getSize() < self::MIN_FREE_BYTES) {
            return response()->json(['success' => false, 'message' => 'The server is almost out of storage space. Delete unused media or ask the developer to add space.'], 507);
        }
        $isVideo = str_starts_with((string) $file->getMimeType(), 'video/');
        if (! $isVideo && $file->getSize() > self::IMAGE_MAX_KB * 1024) {
            return response()->json(['success' => false, 'message' => 'Photos can be up to '.(self::IMAGE_MAX_KB / 1024).' MB.'], 422);
        }

        $extension = $file->guessExtension() ?: ($isVideo ? 'mp4' : 'jpg');
        $extension = $extension === 'qt' ? 'mov' : $extension;
        $folder = 'media/'.now()->format('Y/m');
        $path = $file->storeAs($folder, Str::uuid().'.'.$extension, 'public');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $media = Media::create([
            'type' => $isVideo ? 'video' : 'image',
            'source' => 'upload',
            'url' => '/storage/'.$path,
            'path' => $path,
            'title' => $request->input('title') ?: Str::limit($originalName, 190, ''),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $request->user()?->id,
        ]);

        return response()->json(['success' => true, 'data' => $this->present($media, [])], 201);
    }

    public function addLink(Request $request): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'string', 'max:500'],
            'title' => ['nullable', 'string', 'max:200'],
        ]);

        $url = trim($request->input('url'));
        $video = VideoLink::parse($url);
        if (! $video || $video['source'] === 'upload') {
            return response()->json(['success' => false, 'message' => 'Paste a YouTube, Facebook or Vimeo video link (starting with https://).'], 422);
        }

        $media = Media::firstOrCreate(['url' => $url], [
            'type' => 'video',
            'source' => $video['source'],
            'thumbnail_url' => $video['thumbnail'],
            'title' => $request->input('title') ?: ucfirst($video['source']).' video',
            'uploaded_by' => $request->user()?->id,
        ]);

        return response()->json(['success' => true, 'data' => $this->present($media, $this->usageFor([$media]))], $media->wasRecentlyCreated ? 201 : 200);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $media = Media::findOrFail($id);
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:200'],
            'thumbnail_url' => ['nullable', 'string', 'max:500'],
        ]);
        if (array_key_exists('thumbnail_url', $data) && ! VideoLink::isPoster($data['thumbnail_url'])) {
            return response()->json(['success' => false, 'message' => 'The cover image must be a library photo or an https:// link.'], 422);
        }
        $media->update($data);

        return response()->json(['success' => true, 'data' => $this->present($media, $this->usageFor([$media]))]);
    }

    public function destroy(int $id): JsonResponse
    {
        $media = Media::findOrFail($id);
        $usedIn = $this->usageFor([$media])[$media->url] ?? [];
        if ($usedIn) {
            return response()->json([
                'success' => false,
                'message' => 'It is still in use in '.count($usedIn).' place(s). Remove it there first.',
                'used_in' => $usedIn,
            ], 409);
        }

        if ($media->source === 'upload' && $media->path && str_starts_with($media->path, 'media/')) {
            Storage::disk('public')->delete($media->path);
        }
        $media->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Where each of the given media items is used: listing photos, listing videos and covers, photos waiting in an
     * owner's pending edit, and covers of other library videos. Only listings that can contain these URLs are loaded.
     *
     * @param  iterable<Media>  $items
     * @return array<string, list<array{id: int, title: string, as: string, kind: string}>>
     */
    private function usageFor(iterable $items): array
    {
        $urls = collect($items)->pluck('url')->filter()->unique()->values();
        if ($urls->isEmpty()) {
            return [];
        }
        $wanted = array_flip($urls->all());

        $properties = Property::query()
            ->select(['id', 'title', 'images', 'video_url', 'video_poster', 'owner_pending_changes'])
            ->where(function ($query) use ($urls) {
                $query->whereIn('video_url', $urls)->orWhereIn('video_poster', $urls);
                // JSON columns store "/" escaped, so search by file name, then confirm exact matches below.
                foreach ($urls as $url) {
                    if (str_starts_with($url, '/')) {
                        $needle = '%'.addcslashes(basename($url), '%_\\').'%';
                        $query->orWhere('images', 'like', $needle)->orWhere('owner_pending_changes', 'like', $needle);
                    }
                }
            })
            ->get();

        $usage = [];
        $add = function (string $url, int $id, string $title, string $as, string $kind) use (&$usage, $wanted): void {
            if (isset($wanted[$url])) {
                $usage[$url][] = ['id' => $id, 'title' => $title, 'as' => $as, 'kind' => $kind];
            }
        };
        foreach ($properties as $property) {
            $title = (string) $property->title;
            foreach ((array) $property->images as $url) {
                if (is_string($url)) {
                    $add($url, $property->id, $title, 'photo', 'property');
                }
            }
            if ($property->video_url) {
                $add($property->video_url, $property->id, $title, 'video', 'property');
            }
            if ($property->video_poster) {
                $add($property->video_poster, $property->id, $title, 'video cover', 'property');
            }
            $pending = (array) $property->owner_pending_changes;
            array_walk_recursive($pending, function ($value) use ($add, $property, $title): void {
                if (is_string($value)) {
                    $add($value, $property->id, $title, 'photo in owner\'s pending edit', 'property');
                }
            });
        }

        Media::query()->whereIn('thumbnail_url', $urls)->get(['id', 'title', 'thumbnail_url'])
            ->each(fn (Media $video) => $add((string) $video->thumbnail_url, $video->id, (string) $video->title, 'cover of a library video', 'media'));

        return $usage;
    }

    /**
     * @param  array<string, list<array{id: int, title: string, as: string, kind: string}>>  $usage
     * @return array<string, mixed>
     */
    private function present(Media $media, array $usage): array
    {
        return [
            'id' => $media->id,
            'type' => $media->type,
            'source' => $media->source,
            'url' => $media->url,
            'thumbnail_url' => $media->thumbnail_url,
            'title' => $media->title,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'created_at' => $media->created_at?->toIso8601String(),
            'used_in' => $usage[$media->url] ?? [],
        ];
    }
}
