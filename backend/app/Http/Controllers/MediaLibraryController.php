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

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['nullable', 'in:image,video'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Media::query()->latest('id');
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->input('q').'%');
        }

        $items = $query->limit(500)->get();
        $usage = $this->usageByUrl();

        return response()->json([
            'success' => true,
            'data' => $items->map(fn (Media $media) => $this->present($media, $usage)),
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

        return response()->json(['success' => true, 'data' => $this->present($media, $this->usageByUrl())], $media->wasRecentlyCreated ? 201 : 200);
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

        return response()->json(['success' => true, 'data' => $this->present($media, $this->usageByUrl())]);
    }

    public function destroy(int $id): JsonResponse
    {
        $media = Media::findOrFail($id);
        $usedIn = $this->usageByUrl()[$media->url] ?? [];
        if ($usedIn) {
            return response()->json([
                'success' => false,
                'message' => 'Remove it from '.count($usedIn).' listing(s) first.',
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
     * @return array<string, list<array{id: int, title: string, as: string}>>
     */
    private function usageByUrl(): array
    {
        $usage = [];
        $properties = Property::query()->select(['id', 'title', 'images', 'video_url', 'video_poster'])->get();
        foreach ($properties as $property) {
            foreach ((array) $property->images as $url) {
                if (is_string($url)) {
                    $usage[$url][] = ['id' => $property->id, 'title' => (string) $property->title, 'as' => 'photo'];
                }
            }
            if ($property->video_url) {
                $usage[$property->video_url][] = ['id' => $property->id, 'title' => (string) $property->title, 'as' => 'video'];
            }
            if ($property->video_poster) {
                $usage[$property->video_poster][] = ['id' => $property->id, 'title' => (string) $property->title, 'as' => 'video cover'];
            }
        }

        return $usage;
    }

    /**
     * @param  array<string, list<array{id: int, title: string, as: string}>>  $usage
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
