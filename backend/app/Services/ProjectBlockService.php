<?php

namespace App\Services;

use App\Models\ProjectBlock;
use Illuminate\Support\Facades\DB;

class ProjectBlockService
{
    public function create(array $data): ProjectBlock
    {
        return DB::transaction(fn () => ProjectBlock::create($data));
    }

    public function update(ProjectBlock $projectBlock, array $data): ProjectBlock
    {
        return DB::transaction(function () use ($projectBlock, $data) {
            $projectBlock->update($data);
            return $projectBlock->fresh();
        });
    }

    public function delete(ProjectBlock $projectBlock): bool
    {
        return DB::transaction(fn () => $projectBlock->delete());
    }
}
