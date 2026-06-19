<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }
}
