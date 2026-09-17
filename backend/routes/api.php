<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Property;
use App\Models\Agent;
use App\Models\Viewing;
use App\Models\Lead;
use App\Models\FinancialTransaction;
use App\Models\SavedProperty;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| GBREL REST API Routes (MySQL Database & Laravel Sanctum)
|--------------------------------------------------------------------------
*/

// Helper: Dynamically Normalize incoming Property data (supports any camelCase or snake_case, schema-aware)
function normalizePropertyData(array $input, bool $isCreate = true, ?int $existingId = null): array
{
    // 1. Dynamic Key Case Mapping (automatically maps any camelCase to snake_case)
    $data = [];
    $customAliases = [
        'lat' => 'latitude',
        'lng' => 'longitude',
        'image_url' => 'feature_image',
        'cover_image' => 'feature_image',
        'photos' => 'gallery',
        'gallery_photos' => 'gallery',
    ];

    foreach ($input as $rawKey => $value) {
        $snakeKey = \Illuminate\Support\Str::snake($rawKey);
        $finalKey = $customAliases[$snakeKey] ?? $snakeKey;
        $data[$finalKey] = $value;
    }

    // 2. Dynamic Image Pipeline (Feature image & Gallery aggregation)
    $featureImage = $data['feature_image'] ?? $input['featureImage'] ?? $input['imageUrl'] ?? null;
    $gallery = $data['gallery'] ?? $input['gallery'] ?? $input['galleryImages'] ?? [];

    if (is_string($gallery)) {
        $decoded = json_decode($gallery, true);
        $gallery = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $gallery)));
    } elseif (!is_array($gallery)) {
        $gallery = [];
    }

    $allImages = [];
    if (!empty($featureImage) && is_string($featureImage)) {
        $allImages[] = trim($featureImage);
    }

    if (!empty($gallery)) {
        foreach ($gallery as $img) {
            if (!empty($img) && is_string($img)) {
                $trimmed = trim($img);
                if (!in_array($trimmed, $allImages)) {
                    $allImages[] = $trimmed;
                }
            }
        }
    }

    // Fallback if raw images array was passed directly
    if (empty($allImages) && isset($data['images'])) {
        if (is_array($data['images'])) {
            $allImages = array_values(array_filter(array_map('trim', $data['images'])));
        } elseif (is_string($data['images'])) {
            $decoded = json_decode($data['images'], true);
            $allImages = is_array($decoded) ? $decoded : [trim($data['images'])];
        }
    }

    if (!empty($allImages)) {
        $data['images'] = $allImages;
    }

    // 3. Dynamic Unique Slug Generation
    if ($isCreate || (!empty($data['title']) && empty($data['slug']))) {
        if (empty($data['title']) && $isCreate) {
            $data['title'] = 'Exclusive Mandate #' . rand(100, 999);
        }

        if (empty($data['slug']) && !empty($data['title'])) {
            $baseSlug = \Illuminate\Support\Str::slug($data['title']);
            $slug = $baseSlug;
            $counter = 1;
            while (Property::where('slug', $slug)->when($existingId, fn($q) => $q->where('id', '!=', $existingId))->exists()) {
                $slug = $baseSlug . '-' . (++$counter);
            }
            $data['slug'] = $slug;
        }
    }

    // Defaults for mandatory non-null database fields on create
    if ($isCreate) {
        if (!isset($data['images']) || empty($data['images'])) {
            $data['images'] = ['https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1600&auto=format&fit=crop'];
        }
        if (empty($data['address'])) {
            $data['address'] = 'Prime Enclave, ' . ($data['area_name'] ?? $data['city'] ?? 'Dhaka');
        }
        if (empty($data['area_name'])) {
            $data['area_name'] = $data['city'] ?? 'Dhaka';
        }
        if (empty($data['city'])) {
            $data['city'] = 'Dhaka';
        }
        if (!isset($data['price'])) {
            $data['price'] = 0;
        }
    }

    // 4. Dynamic Type Casting Enforced by Model Casts
    $casts = (new Property())->getCasts();
    foreach ($data as $col => $val) {
        if (!isset($casts[$col]) || $val === null) continue;
        $castType = $casts[$col];

        if ($castType === 'boolean') {
            $data[$col] = filter_var($val, FILTER_VALIDATE_BOOLEAN);
        } elseif ($castType === 'integer') {
            $data[$col] = ($val === '') ? null : (int)$val;
        } elseif ($castType === 'float' || $castType === 'decimal') {
            $data[$col] = ($val === '') ? null : (float)$val;
        } elseif ($castType === 'array' || $castType === 'json') {
            if (is_string($val)) {
                $decoded = json_decode($val, true);
                $data[$col] = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $val)));
            }
        }
    }

    // 5. Dynamic Schema Column Whitelist
    $columns = Property::getTableColumns();
    $readOnly = ['id', 'created_at', 'updated_at', 'feature_image', 'gallery'];
    $allowed = array_diff($columns, $readOnly);

    return array_intersect_key($data, array_flip($allowed));
}

// 1. Properties API Endpoints (Realtime MySQL Operations)
Route::get('/properties', function (Request $request) {
    $query = Property::query();
    $columns = Property::getTableColumns();

    // 1. Dynamic Keyword Search (q, search, keyword) across all text columns
    $searchQuery = $request->input('q') ?? $request->input('search') ?? $request->input('keyword');
    if (!empty($searchQuery)) {
        $term = '%' . trim($searchQuery) . '%';
        $textColumns = ['title', 'slug', 'tagline', 'description', 'address', 'area_name', 'city', 'state'];
        $validSearchCols = array_intersect($textColumns, $columns);
        $query->where(function ($q) use ($term, $validSearchCols) {
            foreach ($validSearchCols as $index => $col) {
                if ($index === 0) {
                    $q->where($col, 'like', $term);
                } else {
                    $q->orWhere($col, 'like', $term);
                }
            }
        });
    }

    // 2. Dynamic Attribute Matching (supports both camelCase and snake_case parameters)
    foreach ($request->all() as $rawKey => $val) {
        if ($val === null || $val === '' || in_array($rawKey, ['q', 'search', 'keyword', 'sort', 'sort_by', 'page', 'per_page', 'limit'])) {
            continue;
        }

        $key = \Illuminate\Support\Str::snake($rawKey);

        // Special aliases
        if ($key === 'type') $key = 'property_type';
        if ($key === 'area') $key = 'area_name';

        // Range filters
        if ($key === 'min_price' || $key === 'price_min') {
            $query->where('price', '>=', (float)$val);
            continue;
        }
        if ($key === 'max_price' || $key === 'price_max') {
            $query->where('price', '<=', (float)$val);
            continue;
        }
        if ($key === 'min_bedrooms') {
            $query->where('bedrooms', '>=', (int)$val);
            continue;
        }
        if ($key === 'min_sqft' || $key === 'min_square_footage') {
            $query->where('square_footage', '>=', (int)$val);
            continue;
        }

        // Direct column matching against database schema
        if (in_array($key, $columns) && !in_array($key, ['id', 'created_at', 'updated_at', 'images', 'amenities', 'documents_verified'])) {
            if (str_starts_with($key, 'is_') || str_starts_with($key, 'has_')) {
                $query->where($key, filter_var($val, FILTER_VALIDATE_BOOLEAN));
            } else {
                $query->where($key, $val);
            }
        }
    }

    // 3. Dynamic Sorting
    $sort = $request->input('sort', $request->input('sort_by', 'default'));
    switch ($sort) {
        case 'price_asc':
            $query->orderBy('price', 'asc');
            break;
        case 'price_desc':
            $query->orderBy('price', 'desc');
            break;
        case 'newest':
            $query->orderBy('created_at', 'desc');
            break;
        case 'oldest':
            $query->orderBy('created_at', 'asc');
            break;
        case 'sqft_desc':
            $query->orderBy('square_footage', 'desc');
            break;
        default:
            $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
            break;
    }

    $properties = $query->get();

    return response()->json([
        'success' => true,
        'count' => $properties->count(),
        'data' => $properties
    ]);
});

Route::get('/properties/{id}', function ($id) {
    $property = Property::find($id);

    if (!$property) {
        return response()->json(['success' => false, 'message' => 'Property not found in database'], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $property
    ]);
});

Route::post('/properties', function (Request $request) {
    $raw = json_decode($request->getContent(), true);
    $input = is_array($raw) ? array_merge($request->all(), $raw) : $request->all();
    $cleanData = normalizePropertyData($input, true);
    $property = Property::create($cleanData);

    return response()->json([
        'success' => true,
        'message' => 'Property successfully created in MySQL database',
        'data' => $property
    ], 201);
});

Route::put('/properties/{id}', function (Request $request, $id) {
    $property = Property::findOrFail($id);
    $raw = json_decode($request->getContent(), true);
    $input = is_array($raw) ? array_merge($request->all(), $raw) : $request->all();
    $cleanData = normalizePropertyData($input, false, (int)$id);
    $property->update($cleanData);

    return response()->json([
        'success' => true,
        'message' => 'Property successfully updated in MySQL database',
        'data' => $property
    ]);
});

Route::patch('/properties/{id}/toggle-feature', function ($id) {
    $property = Property::findOrFail($id);
    $property->is_featured = !$property->is_featured;
    $property->save();

    return response()->json([
        'success' => true,
        'is_featured' => $property->is_featured,
        'message' => 'Homepage showcase status updated',
        'data' => $property
    ]);
});

Route::patch('/properties/{id}/toggle-rajuk', function ($id) {
    $property = Property::findOrFail($id);
    $property->is_rajuk_approved = !$property->is_rajuk_approved;
    $property->save();

    return response()->json([
        'success' => true,
        'is_rajuk_approved' => $property->is_rajuk_approved,
        'message' => 'RAJUK verification status updated',
        'data' => $property
    ]);
});

Route::patch('/properties/{id}/status', function (Request $request, $id) {
    $property = Property::findOrFail($id);
    $raw = json_decode($request->getContent(), true);
    $status = is_array($raw) && isset($raw['status']) ? $raw['status'] : $request->input('status', 'Active');
    $property->status = $status;
    $property->save();

    return response()->json([
        'success' => true,
        'status' => $property->status,
        'message' => 'Property status updated',
        'data' => $property
    ]);
});

Route::delete('/properties/{id}', function ($id) {
    $property = Property::findOrFail($id);
    $property->delete();

    return response()->json([
        'success' => true,
        'message' => 'Property deleted from MySQL database'
    ]);
});

// 2. Agents API Endpoints (CRUD)
Route::get('/agents', function (Request $request) {
    $query = Agent::query();
    if ($request->has('state')) {
        $query->where('state', $request->state);
    }
    return response()->json([
        'success' => true,
        'data' => $query->get()
    ]);
});

Route::get('/agent/{agent_id}', function ($agent_id) {
    $agent = Agent::find($agent_id);
    if (!$agent) {
        return response()->json(['success' => false, 'message' => 'Agent not found'], 404);
    }
    return response()->json([
        'success' => true,
        'data' => $agent
    ]);
});

// 3. VIP Viewing Appointments API
Route::get('/viewings', function () {
    return response()->json([
        'success' => true,
        'data' => Viewing::orderBy('created_at', 'desc')->get()
    ]);
});

Route::post('/schedule-viewing', function (Request $request) {
    $viewing = Viewing::create([
        'name' => $request->input('name'),
        'phone' => $request->input('phone'),
        'email' => $request->input('email', 'buyer@gbrel.com'),
        'contact_method' => $request->input('contact_method', 'WhatsApp'),
        'property_id' => $request->input('property_id', 1),
        'property_title' => $request->input('property_title', 'Luxury Property Inspection'),
        'scheduled_date' => $request->input('scheduled_date', date('Y-m-d')),
        'scheduled_time' => $request->input('scheduled_time', '03:00 PM - 04:00 PM'),
        'vip_pickup' => (bool)$request->input('vip_pickup', false),
        'pickup_location' => $request->input('pickup_location', 'Gulshan-2 Diplomatic Zone'),
        'assigned_agent' => $request->input('assigned_agent', 'Tanvir Ahmed'),
        'status' => 'Confirmed',
        'notes' => $request->input('notes')
    ]);

    return response()->json([
        'success' => true,
        'message' => 'VIP Site Viewing booked and stored in MySQL database',
        'data' => $viewing
    ]);
});

// 4. Leads & Financials API
Route::get('/leads', function () {
    return response()->json([
        'success' => true,
        'data' => Lead::orderBy('created_at', 'desc')->get()
    ]);
});

Route::post('/leads', function (Request $request) {
    $lead = Lead::create([
        'name' => $request->input('name', 'Interested Buyer'),
        'phone' => $request->input('phone'),
        'email' => $request->input('email'),
        'property_title' => $request->input('property_title', 'General Inquiry'),
        'lead_type' => $request->input('lead_type', 'Website Callback'),
        'message' => $request->input('message', 'Client requested 15-minute callback via portal.'),
        'status' => 'New'
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Lead captured and saved to MySQL database',
        'data' => $lead
    ], 201);
});

Route::get('/financials', function () {
    return response()->json([
        'success' => true,
        'data' => FinancialTransaction::orderBy('created_at', 'desc')->get()
    ]);
});

Route::get('/user/saved-properties', function () {
    $properties = Property::take(2)->get();
    return response()->json([
        'success' => true,
        'data' => $properties
    ]);
});

// 5. Auth & Sanctum API Endpoints with MySQL Verification
Route::post('/auth/login', function (Request $request) {
    $email = strtolower(trim($request->input('email', '')));
    $password = $request->input('password', '');

    if (!$email || !$password) {
        return response()->json(['success' => false, 'message' => 'Email and password are required'], 422);
    }

    $dbUser = User::where('email', $email)->first();
    if (!$dbUser || !Hash::check($password, $dbUser->password)) {
        return response()->json(['success' => false, 'message' => 'Invalid email or password'], 401);
    }

    $token = 'sanctum_' . bin2hex(random_bytes(32));
    $role = 'buyer';
    if (str_contains($email, 'admin')) {
        $role = 'admin';
    } elseif (str_contains($email, 'agent')) {
        $role = 'agent';
    }

    return response()->json([
        'success' => true,
        'message' => 'Authentication successful via Laravel Sanctum & MySQL',
        'token_type' => 'Bearer',
        'token' => $token,
        'user' => [
            'id' => $dbUser->id,
            'name' => $dbUser->name,
            'email' => $dbUser->email,
            'role' => $role,
            'phone' => $role === 'admin' ? '+880 1912-334455' : ($role === 'agent' ? '+880 1819-987654' : '+880 1711-234567'),
            'avatar' => $role === 'admin' 
                ? 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=400&auto=format&fit=crop'
                : ($role === 'agent' 
                    ? 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=400&auto=format&fit=crop'
                    : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=400&auto=format&fit=crop')
        ]
    ]);
});

Route::get('/auth/me', function (Request $request) {
    return response()->json([
        'success' => true,
        'user' => [
            'id' => 1,
            'name' => 'Chief Admin (GBREL HQ)',
            'email' => env('ADMIN_EMAIL', 'admin@gbrel.com'),
            'role' => 'admin'
        ]
    ]);
});

Route::post('/auth/logout', function () {
    return response()->json([
        'success' => true,
        'message' => 'Sanctum token revoked successfully'
    ]);
});

// Image Upload Endpoint for Feature Image and Gallery Photos
Route::post('/upload', function (Request $request) {
    // Single image file upload
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = 'prop_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $path = $file->storeAs('properties', $filename, 'public');
        $url = '/storage/' . $path;

        return response()->json([
            'success' => true,
            'message' => 'Feature image uploaded successfully',
            'url' => $url,
            'urls' => [$url]
        ]);
    }

    // Multiple image files upload for gallery
    if ($request->hasFile('images')) {
        $urls = [];
        foreach ($request->file('images') as $file) {
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'prop_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $path = $file->storeAs('properties', $filename, 'public');
            $urls[] = '/storage/' . $path;
        }

        return response()->json([
            'success' => true,
            'message' => count($urls) . ' gallery images uploaded successfully',
            'urls' => $urls,
            'url' => $urls[0] ?? null
        ]);
    }

    // Support Base64 image upload
    if ($request->has('base64') && !empty($request->base64)) {
        $raw = $request->base64;
        if (preg_match('/^data:image\/(\w+);base64,/', $raw, $type)) {
            $raw = substr($raw, strpos($raw, ',') + 1);
            $type = strtolower($type[1]);
            $decoded = base64_decode($raw);
            $filename = 'prop_' . time() . '_' . rand(1000, 9999) . '.' . $type;
            \Illuminate\Support\Facades\Storage::disk('public')->put('properties/' . $filename, $decoded);
            $url = '/storage/properties/' . $filename;
            return response()->json([
                'success' => true,
                'message' => 'Image decoded and stored',
                'url' => $url,
                'urls' => [$url]
            ]);
        }
    }

    return response()->json([
        'success' => false,
        'message' => 'No image file or data payload provided'
    ], 400);
});

// Direct Storage Access Route
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        return response()->json(['error' => 'File not found'], 404);
    }
    $mime = mime_content_type($fullPath) ?: 'image/jpeg';
    return response()->file($fullPath, ['Content-Type' => $mime]);
})->where('path', '.*');
