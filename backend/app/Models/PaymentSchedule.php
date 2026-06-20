<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sale_id',
        'installment_no',
        'due_date',
        'amount_due',
        'amount_paid',
        'balance',
        'status',
        'remarks',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }
}
