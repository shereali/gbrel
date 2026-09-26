<?php

namespace App\Http\Controllers;

use App\Models\PropertyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PropertyDocumentController extends Controller
{
    /**
     * Streams a private ownership document to its owner or to reviewing staff.
     */
    public function download(Request $request, int $documentId): Response
    {
        $document = PropertyDocument::with('property:id,owner_id')->findOrFail($documentId);
        $user = $request->user();

        $isOwner = $document->property && $document->property->owner_id === $user->id;
        $isReviewer = $user->isStaff() && ($user->hasPermission('listings.review') || $user->hasPermission('properties.edit'));
        abort_unless($isOwner || $isReviewer, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->response($document->file_path, $document->original_name, [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
