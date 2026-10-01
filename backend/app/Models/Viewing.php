<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Viewing extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'vip_pickup' => 'boolean',
        'scheduled_date' => 'date',
        'scheduled_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
