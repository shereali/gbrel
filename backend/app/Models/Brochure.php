<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brochure extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'download_count' => 'integer',
        'is_public' => 'boolean'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
