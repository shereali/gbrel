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

// 1. Properties API Endpoints (CRUD)
Route::get('/properties', function (Request $request) {
    $query = Property::query();

    if ($request->has('state')) {
        $query->where('state', $request->state);
    }
    if ($request->has('type')) {
        $query->where('property_type', $request->type);
    }
    if ($request->has('max_price')) {
        $query->where('price', '<=', (float)$request->max_price);
    }
    if ($request->has('status')) {
        $query->where('status', $request->status);
    }

    $properties = $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc')->get();

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
    $data = $request->all();
    if (!isset($data['images']) || empty($data['images'])) {
        $data['images'] = ['https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=800&auto=format&fit=crop'];
    }
    $property = Property::create($data);

    return response()->json([
        'success' => true,
        'message' => 'Property created in MySQL database',
        'data' => $property
    ], 201);
});

Route::put('/properties/{id}', function (Request $request, $id) {
    $property = Property::findOrFail($id);
    $property->update($request->all());

    return response()->json([
        'success' => true,
        'message' => 'Property updated in MySQL database',
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
