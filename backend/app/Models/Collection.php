<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Collection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'collection_no',
        'sale_id',
        'payment_schedule_id',
        'official_receipt_no',
        'payment_date',
        'amount_paid',
        'payment_method',
        'reference_no',
        'status',
        'remarks',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount_paid' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function paymentSchedule(): BelongsTo
    {
        return $this->belongsTo(PaymentSchedule::class);
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function voidedByUser()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
