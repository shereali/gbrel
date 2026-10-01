<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorEvent extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
        'occurred_at' => 'datetime',
    ];
}
