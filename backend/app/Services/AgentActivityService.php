<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AgentActivityService
{
    public static function log(
        Agent $agent,
        string $type,
        string $title,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {

        $request = request();

        AgentActivity::create([
            'agent_id'      => $agent->id,
            'user_id'       => Auth::id(),
            'activity_type' => $type,
            'title'         => $title,
            'description'   => $description,
            'old_values'    => $oldValues,
            'new_values'    => $newValues,
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
        ]);
    }
}
