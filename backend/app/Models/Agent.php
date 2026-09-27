<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agent extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'rating' => 'float',
        'experience_years' => 'integer',
        'review_count' => 'integer',
        'active_listings_count' => 'integer',
        'specialties' => 'array'
    ];

    public function properties()
    {
        return $this->hasMany(Property::class);
    }
}
