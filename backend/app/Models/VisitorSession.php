<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorSession extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'in_facebook_app' => 'boolean',
    ];
}
