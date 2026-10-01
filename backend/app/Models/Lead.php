<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'next_follow_up_at' => 'datetime',
        'last_contacted_at' => 'datetime',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }
}
