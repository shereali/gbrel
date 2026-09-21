<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $guarded = ['id'];

    public function properties()
    {
        return $this->hasMany(Property::class, 'property_type', 'name');
    }
}
