<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyDocument;
use App\Support\OwnerListing;
use App\Support\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * A property owner's own listings. Owners never see buyers or leads; GBREL reviews, publishes and sells.
 */
class OwnerListingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $listings = Property::where('owner_id', $request->user()->id)
            ->with('documents')
            ->latest()
            ->get()
            ->map(fn (Property $property) => $this->present($property));

        return response()->json(['success' => true, 'data' => $listings]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->present($this->ownedProperty($request, $id))]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(OwnerListing::rules(isDraft: true));
        $attributes = OwnerListing::toAttributes($validated);

        $property = Property::create(array_merge([
            'title' => 'নতুন প্রপার্টি',
            'address' => '',
            'area_name' => '',
            'price' => 0,
        ], array_filter($attributes, fn ($value) => $value !== null), [
            'slug' => 'owner-'.Str::lower(Str::random(10)),
            'status' => 'Draft',
            'listing_type' => 'Sale',
            'is_verified' => false,
            'is_featured' => false,
            'owner_id' => $request->user()->id,
            'review_status' => 'draft',
        ]));

        return response()->json(['success' => true, 'data' => $this->present($property->fresh())], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $property = $this->ownedProperty($request, $id);
        $validated = $request->validate(OwnerListing::rules(isDraft: true));
        $attributes = OwnerListing::toAttributes($validated, $property);

        if (in_array($property->review_status, OwnerListing::EDITABLE_STATES, true)) {
            $property->update(array_filter($attributes, fn ($value, $key) => $value !== null || in_array($key, ['address', 'description'], true), ARRAY_FILTER_USE_BOTH));
        } elseif ($property->published_at !== null || $property->review_status === 'approved' || $property->review_status === 'update_submitted') {
            // Published or approved listings stay as they are; GBREL reviews the owner's changes first.
            $property->update([
                'owner_pending_changes' => array_merge($property->owner_pending_changes ?? [], $attributes),
                'review_status' => 'update_submitted',
            ]);
        } else {
            throw ValidationException::withMessages(['listing' => 'এই মুহূর্তে প্রপার্টিটি যাচাই চলছে। পরিবর্তনের জন্য GBREL টিমের সঙ্গে যোগাযোগ করুন।']);
        }

        return response()->json(['success' => true, 'data' => $this->present($property->fresh())]);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $property = $this->ownedProperty($request, $id);
        if (! in_array($property->review_status, ['draft', 'changes_requested'], true)) {
            throw ValidationException::withMessages(['listing' => 'প্রপার্টিটি আগেই জমা দেওয়া হয়েছে।']);
        }

        $request->validate(['accept_terms' => ['accepted'], 'terms_version' => ['required', 'string', 'max:40']], [
            'accept_terms.accepted' => 'জমা দেওয়ার আগে শর্তগুলোতে সম্মতি দিন।',
        ]);

        // Everything the review team needs must be filled before submitting.
        $current = array_merge($property->only(OwnerListing::PROPERTY_FIELDS), ['owner_details' => $property->owner_details ?? []]);
        validator($current, OwnerListing::rules(isDraft: false))->validate();

        $settings = SiteSettings::all();
        if ($request->input('terms_version') !== $settings['owner_terms_version']) {
            throw ValidationException::withMessages(['accept_terms' => 'শর্তগুলো হালনাগাদ হয়েছে। পেজটি আবার লোড করে নতুন শর্ত পড়ে নিন।']);
        }

        $property->update([
            'review_status' => 'submitted',
            'status' => 'Pending Review',
            'submitted_at' => now(),
            'owner_agreement' => [
                'accepted_at' => now()->toIso8601String(),
                'terms_version' => $settings['owner_terms_version'],
                'commission_percent' => (float) $settings['owner_commission_percent'],
                'terms' => $settings['owner_terms'],
                'ip' => $request->ip(),
            ],
        ]);

        return response()->json(['success' => true, 'data' => $this->present($property->fresh())]);
    }

    public function uploadDocument(Request $request, int $id): JsonResponse
    {
        $property = $this->ownedProperty($request, $id);
        $types = collect(SiteSettings::documentTypes())->pluck('key')->all();

        $validated = $request->validate([
            'document_type' => ['required', Rule::in($types)],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:15360'],
        ], [
            'file.mimes' => 'PDF বা ছবি (JPG, PNG) দিন।',
            'file.max' => 'ফাইলটি ১৫ MB-এর কম হতে হবে।',
        ]);

        $file = $validated['file'];
        $path = $file->storeAs(
            'property-documents/'.$property->id,
            Str::uuid()->toString().'.'.($file->guessExtension() ?: 'bin'),
            'local'
        );

        $document = $property->documents()->create([
            'uploaded_by' => $request->user()->id,
            'document_type' => $validated['document_type'],
            'file_path' => $path,
            'original_name' => Str::limit($file->getClientOriginalName(), 180, ''),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'data' => $document], 201);
    }

    public function deleteDocument(Request $request, int $id, int $documentId): JsonResponse
    {
        $property = $this->ownedProperty($request, $id);
        $document = $property->documents()->findOrFail($documentId);

        if ($document->status === 'verified') {
            throw ValidationException::withMessages(['document' => 'যাচাই করা কাগজ মুছে ফেলা যাবে না। প্রয়োজনে GBREL টিমকে জানান।']);
        }

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return response()->json(['success' => true]);
    }

    private function ownedProperty(Request $request, int $id): Property
    {
        return Property::where('owner_id', $request->user()->id)->with('documents')->findOrFail($id);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Property $property): array
    {
        $property->loadMissing('documents');
        $data = $property->withPrivateFields()->toArray();
        unset($data['reviewed_by'], $data['owner_agreement']['ip']);
        $data['documents'] = $property->documents->map(fn (PropertyDocument $document) => $document->only([
            'id', 'document_type', 'original_name', 'mime_type', 'size', 'status', 'review_note', 'created_at',
        ]))->all();
        $data['missing_required_documents'] = OwnerListing::missingRequiredDocuments($property);
        $data['is_live'] = $property->isVisibleToPublic();

        return $data;
    }
}
