<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'float',
        'land_size' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'balconies' => 'integer',
        'square_footage' => 'integer',
        'parking' => 'integer',
        'floor_number' => 'integer',
        'total_floors' => 'integer',
        'year_built' => 'integer',
        'agent_id' => 'integer',
        'is_featured' => 'boolean',
        'is_rajuk_approved' => 'boolean',
        'is_verified' => 'boolean',
        'has_open_house' => 'boolean',
        'images' => 'array',
        'amenities' => 'array',
        'documents_verified' => 'array'
    ];

    protected $appends = ['feature_image', 'gallery'];

    public static function getTableColumns(): array
    {
        static $columns = null;
        if ($columns === null) {
            try {
                $columns = \Illuminate\Support\Facades\Schema::getColumnListing((new static)->getTable());
            } catch (\Throwable $e) {
                $columns = [
                    'id', 'title', 'slug', 'tagline', 'description', 'address', 'city', 'state', 'area_name',
                    'price', 'price_unit', 'listing_type', 'property_type', 'status', 'bedrooms', 'bathrooms',
                    'balconies', 'square_footage', 'land_size', 'land_unit', 'parking', 'floor_number',
                    'total_floors', 'facing', 'completion_status', 'year_built', 'is_featured',
                    'is_rajuk_approved', 'is_verified', 'has_open_house', 'latitude', 'longitude',
                    'agent_id', 'images', 'amenities', 'documents_verified', 'brochure_url', 'created_at', 'updated_at'
                ];
            }
        }
        return $columns;
    }

    public function getFeatureImageAttribute(): ?string
    {
        return !empty($this->images) && is_array($this->images) ? $this->images[0] : null;
    }

    public function getGalleryAttribute(): array
    {
        return !empty($this->images) && is_array($this->images) && count($this->images) > 1 
            ? array_values(array_slice($this->images, 1)) 
            : [];
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
