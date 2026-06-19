<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyProject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_code',
        'project_name',
        'description',
        'developer_name',
        'country',
        'province',
        'municipality',
        'barangay',
        'street',
        'status',
    ];

    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(ProjectBlock::class);
    }

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }
}
