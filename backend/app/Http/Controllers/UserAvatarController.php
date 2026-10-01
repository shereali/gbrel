<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Profile photos for team accounts (Admin → Users). Routes require the users.manage permission.
 */
class UserAvatarController extends Controller
{
    public const MAX_KB = 5120;

    private const FOLDER = 'avatars';

    public function store(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:'.self::MAX_KB],
        ], [
            'avatar.mimetypes' => 'Choose a JPG, PNG or WebP photo.',
            'avatar.max' => 'The photo can be up to '.(self::MAX_KB / 1024).' MB.',
        ]);

        $user = User::findOrFail($id);
        $file = $request->file('avatar');
        $path = $file->storeAs(self::FOLDER, Str::uuid().'.'.($file->guessExtension() ?: 'jpg'), 'public');

        $this->deleteStored($user->avatar);
        $user->forceFill(['avatar' => '/storage/'.$path])->save();

        return response()->json(['success' => true, 'data' => ['avatar' => $user->avatar]]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->deleteStored($user->avatar);
        $user->forceFill(['avatar' => null])->save();

        return response()->json(['success' => true, 'data' => ['avatar' => null]]);
    }

    /** Only files this controller stored are deleted; external image links are left alone. */
    private function deleteStored(?string $url): void
    {
        $prefix = '/storage/'.self::FOLDER.'/';
        if ($url && str_starts_with($url, $prefix) && ! str_contains($url, '..')) {
            Storage::disk('public')->delete(self::FOLDER.'/'.substr($url, strlen($prefix)));
        }
    }
}
