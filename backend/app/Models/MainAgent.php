<?php

namespace App\Models;

class MainAgent extends Agent
{
    /**
     * Use the existing agents table.
     */
    protected $table = 'agents';

    /**
     * Always retrieve only main agents.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('main_agents', function ($query) {
            $query->where('agent_type', 'main_agent');
        });
    }
}
