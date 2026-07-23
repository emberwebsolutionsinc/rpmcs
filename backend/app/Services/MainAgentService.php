<?php

namespace App\Services;

use App\Models\Agent;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MainAgentService
{
    public function create(array $data): Agent
    {
        return DB::transaction(function () use ($data) {
            $data['agent_type'] = 'main_agent';
            $data['parent_agent_id'] = null;

            return Agent::create($data);
        });
    }

    public function update(
        Agent $mainAgent,
        array $data
    ): Agent {
        if ($mainAgent->agent_type !== 'main_agent') {
            throw new RuntimeException(
                'The selected agent is not a Main Agent.'
            );
        }

        return DB::transaction(function () use (
            $mainAgent,
            $data
        ) {
            $mainAgent->update([
                'first_name' =>
                    $data['first_name'],

                'middle_name' =>
                    $data['middle_name'] ?? null,

                'last_name' =>
                    $data['last_name'],

                'suffix' =>
                    $data['suffix'] ?? null,

                'contact_number' =>
                    $data['contact_number'] ?? null,

                'email' =>
                    $data['email'] ?? null,

                'address' =>
                    $data['address'] ?? null,

                'default_commission_rate' =>
                    $data['default_commission_rate']
                    ?? null,

                'status' =>
                    $data['status'],
            ]);

            return $mainAgent->fresh();
        });
    }
}
