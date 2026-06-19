<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reservation_no',
        'client_id',
        'lot_id',
        'agent_id',
        'reservation_date',
        'expiration_date',
        'reservation_fee',
        'status',
        'remarks',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'expiration_date' => 'date',
        'reservation_fee' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
