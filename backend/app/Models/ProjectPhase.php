<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectPhase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_project_id',
        'phase_code',
        'phase_name',
        'description',
        'status',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(PropertyProject::class);
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
