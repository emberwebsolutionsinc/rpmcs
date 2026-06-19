<?php

namespace App\Services;

use App\Models\ProjectPhase;
use Illuminate\Support\Facades\DB;

class ProjectPhaseService
{
    public function create(array $data): ProjectPhase
    {
        return DB::transaction(fn () => ProjectPhase::create($data));
    }

    public function update(ProjectPhase $projectPhase, array $data): ProjectPhase
    {
        return DB::transaction(function () use ($projectPhase, $data) {
            $projectPhase->update($data);
            return $projectPhase->fresh();
        });
    }

    public function delete(ProjectPhase $projectPhase): bool
    {
        return DB::transaction(fn () => $projectPhase->delete());
    }
}
