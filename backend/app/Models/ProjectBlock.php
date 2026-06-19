<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectBlock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_project_id',
        'project_phase_id',
        'block_no',
        'description',
        'status',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(PropertyProject::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class);
    }

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }
}
