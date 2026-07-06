<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'user_id',
        'activity_type',
        'title',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
