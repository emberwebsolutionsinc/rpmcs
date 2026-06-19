<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionSetting extends Model
{
    protected $fillable = [
        'default_commission_rate',
        'sub_agent_share_percentage',
        'main_agent_cut_percentage',
        'is_active',
    ];
}
