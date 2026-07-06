<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AgentCommissionPayment;
use App\Models\AgentDocument;
use App\Models\AgentActivity;
use App\Models\Sale;

class Agent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_agent_id',
        'agent_code',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'contact_number',
        'email',
        'address',
        'license_number',
        'default_commission_rate',
        'agent_type',
        'status',
    ];

    public function mainAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'parent_agent_id');
    }

    public function subAgents(): HasMany
    {
        return $this->hasMany(Agent::class, 'parent_agent_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function commissionPayments(): HasMany
    {
        return $this->hasMany(AgentCommissionPayment::class);
    }
    public function documents()
    {
        return $this->hasMany(AgentDocument::class);
    }

    public function activities()
    {
        return $this->hasMany(AgentActivity::class)
            ->latest();
    }

    public function sales(): HasMany
{
    return $this->hasMany(Sale::class, 'agent_id');
}

    public function payments(): HasMany
{
    return $this->commissionPayments();
}



}
