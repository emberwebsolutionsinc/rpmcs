<?php

namespace App\Services;

use App\Models\PropertyProject;
use Illuminate\Support\Facades\DB;

class PropertyProjectService
{
    public function create(array $data): PropertyProject
    {
        return DB::transaction(function () use ($data) {
            return PropertyProject::create($data);
        });
    }

    public function update(PropertyProject $propertyProject, array $data): PropertyProject
    {
        return DB::transaction(function () use ($propertyProject, $data) {
            $propertyProject->update($data);

            return $propertyProject->fresh();
        });
    }

    public function delete(PropertyProject $propertyProject): bool
    {
        return DB::transaction(function () use ($propertyProject) {
            return $propertyProject->delete();
        });
    }
}
