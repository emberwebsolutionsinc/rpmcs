<?php

namespace App\Services;

use App\Models\Agent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgentService
{
    /**
     * Create an agent.
     */
    public function create(array $data): Agent
    {
        return DB::transaction(function () use ($data) {
            return Agent::create($data);
        });
    }

    /**
     * Update a Sub-Agent.
     */
    public function update(
        Agent $agent,
        array $data
    ): Agent {
        return DB::transaction(function () use ($agent, $data) {
            $agent->update([
                'agent_code' => $data['agent_code'],

                'agent_type' => 'sub_agent',

                'parent_agent_id' =>
                    $data['parent_agent_id'],

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
                    $data['default_commission_rate'] ?? 0,

                'status' =>
                    $data['status'],
            ]);

            return $agent->fresh([
                'mainAgent',
            ]);
        });
    }

    /**
     * Delete an agent.
     */
    public function delete(
        Agent $agent
    ): bool {
        return DB::transaction(function () use ($agent) {
            $hasSubAgents = Agent::query()
                ->where(
                    'parent_agent_id',
                    $agent->id
                )
                ->exists();

            if ($hasSubAgents) {
                throw ValidationException::withMessages([
                    'agent' => [
                        'This Main Agent cannot be deleted because it still has existing Sub-Agents.',
                    ],
                ]);
            }

            return (bool) $agent->delete();
        });
    }
}
