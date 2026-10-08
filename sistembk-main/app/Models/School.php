<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = ['name', 'npsn', 'address'];

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
