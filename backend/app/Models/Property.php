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
        'is_featured' => 'boolean',
        'is_rajuk_approved' => 'boolean',
        'is_verified' => 'boolean',
        'has_open_house' => 'boolean',
        'images' => 'array',
        'amenities' => 'array',
        'documents_verified' => 'array'
    ];

    protected $appends = ['feature_image', 'gallery'];

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
