<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Lot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_project_id',
        'project_phase_id',
        'project_block_id',
        'lot_code',
        'lot_no',
        'title_no',
        'lot_area',
        'price_per_sqm',
        'selling_price',
        'corner_lot',
        'road_lot',
        'status',
        'remarks',
        'property_type_id',
    ];

    protected $casts = [
        'corner_lot' => 'boolean',
        'road_lot' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(PropertyProject::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(ProjectBlock::class, 'project_block_id');
    }

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

}
