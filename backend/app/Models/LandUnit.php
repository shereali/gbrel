<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LandUnit extends Model
{
    use HasFactory;

    protected $table = 'land_units';
    protected $guarded = ['id'];
}
