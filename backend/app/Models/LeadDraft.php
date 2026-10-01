<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadDraft extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'answers' => 'array',
        'consent_at' => 'datetime',
        'called_at' => 'datetime',
    ];
}
