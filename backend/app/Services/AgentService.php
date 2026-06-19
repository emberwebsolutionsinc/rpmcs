<?php

namespace App\Services;

use App\Models\Agent;
use Illuminate\Support\Facades\DB;

class AgentService
{
    public function create(array $data): Agent
    {
        return DB::transaction(function () use ($data) {

            return Agent::create($data);

        });
    }

    public function update(
        Agent $agent,
        array $data
    ): Agent {

        return DB::transaction(function () use (
            $agent,
            $data
        ) {

            $agent->update($data);

            return $agent->fresh();

        });
    }

    public function delete(
        Agent $agent
    ): bool {

        return DB::transaction(function () use (
            $agent
        ) {

            return $agent->delete();

        });
    }
}
