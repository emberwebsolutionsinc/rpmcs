<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'sale_no',
        'reservation_id',
        'client_id',
        'lot_id',
        'agent_id',
        'contract_price',
        'downpayment',
        'balance',
        'sale_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'contract_price' => 'decimal:2',
        'downpayment' => 'decimal:2',
        'balance' => 'decimal:2',
        'sale_date' => 'date',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
