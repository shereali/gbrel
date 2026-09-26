<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyDocument;
use App\Support\OwnerListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * GBREL staff review of owner-submitted properties: check papers, ask for changes, approve, publish.
 */
class ListingReviewController extends Controller
{
    public const QUEUE_STATES = ['submitted', 'in_review', 'changes_requested', 'approved', 'update_submitted', 'rejected', 'draft'];

    public function index(Request $request): JsonResponse
    {
        $request->validate(['review_status' => ['nullable', Rule::in(self::QUEUE_STATES)]]);

        $query = Property::whereNotNull('owner_id')->with(['owner:id,name,phone,email', 'documents'])->orderByRaw('submitted_at is null')->orderByDesc('submitted_at')->orderByDesc('updated_at');
        if ($request->filled('review_status')) {
            $query->where('review_status', $request->input('review_status'));
        } else {
            $query->where('review_status', '!=', 'draft');
        }

        $counts = Property::whereNotNull('owner_id')->selectRaw('review_status, count(*) as total')->groupBy('review_status')->pluck('total', 'review_status');

        $items = $query->get()->map(fn (Property $property) => [
            'id' => $property->id,
            'title' => $property->title,
            'property_type' => $property->property_type,
            'area_name' => $property->area_name,
            'city' => $property->city,
            'land_size' => $property->land_size,
            'land_unit' => $property->land_unit,
            'expected_price' => $property->owner_details['expectedPrice'] ?? null,
            'price_basis' => $property->owner_details['priceBasis'] ?? null,
            'review_status' => $property->review_status,
            'status' => $property->status,
            'submitted_at' => $property->submitted_at,
            'updated_at' => $property->updated_at,
            'owner' => $property->owner?->only(['id', 'name', 'phone', 'email']),
            'documents_total' => $property->documents->count(),
            'documents_verified' => $property->documents->where('status', 'verified')->count(),
            'missing_required_documents' => count(OwnerListing::missingRequiredDocuments($property)),
            'has_pending_changes' => ! empty($property->owner_pending_changes),
            'cover' => $property->images[0] ?? null,
        ]);

        return response()->json(['success' => true, 'counts' => $counts, 'data' => $items]);
    }

    public function show(int $id): JsonResponse
    {
        $property = Property::whereNotNull('owner_id')->with(['owner:id,name,phone,email,created_at', 'documents'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $this->present($property)]);
    }

    public function review(Request $request, int $id): JsonResponse
    {
        $property = Property::whereNotNull('owner_id')->findOrFail($id);
        $validated = $request->validate([
            'action' => ['required', Rule::in(['start_review', 'request_changes', 'approve', 'reject', 'publish', 'unpublish'])],
            'note' => ['nullable', 'string', 'max:3000', Rule::requiredIf(in_array($request->input('action'), ['request_changes', 'reject'], true))],
        ], ['note.required' => 'মালিককে কী করতে হবে বা কেন বাতিল হলো, তা লিখুন।']);

        $action = $validated['action'];
        $updates = ['reviewed_at' => now(), 'reviewed_by' => $request->user()->id];
        if (array_key_exists('note', $validated)) {
            $updates['review_note'] = $validated['note'];
        }

        switch ($action) {
            case 'start_review':
                $updates['review_status'] = 'in_review';
                break;
            case 'request_changes':
                $updates += ['review_status' => 'changes_requested', 'status' => 'Draft'];
                break;
            case 'reject':
                $updates += ['review_status' => 'rejected', 'status' => 'Rejected', 'published_at' => null];
                break;
            case 'approve':
                $updates['review_status'] = 'approved';
                break;
            case 'publish':
                if (! in_array($property->review_status, ['approved', 'update_submitted'], true)) {
                    throw ValidationException::withMessages(['action' => 'প্রকাশের আগে প্রপার্টিটি অনুমোদন দিন।']);
                }
                $updates += ['status' => 'Active', 'published_at' => $property->published_at ?? now(), 'is_verified' => true];
                break;
            case 'unpublish':
                $updates += ['status' => 'Delisted', 'published_at' => null];
                break;
        }

        $property->update($updates);

        return response()->json(['success' => true, 'data' => $this->present($property->fresh(['owner', 'documents']))]);
    }

    /**
     * Applies an owner's edits to an already approved or published listing.
     */
    public function applyChanges(Request $request, int $id): JsonResponse
    {
        $property = Property::whereNotNull('owner_id')->findOrFail($id);
        $changes = $property->owner_pending_changes ?? [];
        if ($changes === []) {
            throw ValidationException::withMessages(['changes' => 'মালিকের কোনো নতুন পরিবর্তন নেই।']);
        }

        $request->validate(['decision' => ['required', Rule::in(['apply', 'discard'])]]);
        $updates = ['owner_pending_changes' => null, 'review_status' => 'approved', 'reviewed_at' => now(), 'reviewed_by' => $request->user()->id];
        if ($request->input('decision') === 'apply') {
            $updates = array_merge(array_intersect_key($changes, array_flip(array_merge(OwnerListing::PROPERTY_FIELDS, ['owner_details', 'price']))), $updates);
        }
        $property->update($updates);

        return response()->json(['success' => true, 'data' => $this->present($property->fresh(['owner', 'documents']))]);
    }

    public function reviewDocument(Request $request, int $documentId): JsonResponse
    {
        $document = PropertyDocument::findOrFail($documentId);
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'verified', 'rejected'])],
            'review_note' => ['nullable', 'string', 'max:1000', Rule::requiredIf($request->input('status') === 'rejected')],
        ], ['review_note.required' => 'কেন কাগজটি গ্রহণ করা হলো না, মালিককে জানাতে লিখুন।']);

        $document->update($validated + ['reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);

        return response()->json(['success' => true, 'data' => $document->fresh()]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Property $property): array
    {
        $data = $property->withPrivateFields()->toArray();
        $data['owner'] = $property->owner?->only(['id', 'name', 'phone', 'email', 'created_at']);
        $data['documents'] = $property->documents->map(fn (PropertyDocument $document) => $document->toArray())->all();
        $data['missing_required_documents'] = OwnerListing::missingRequiredDocuments($property);
        $data['is_live'] = $property->isVisibleToPublic();

        return $data;
    }
}
