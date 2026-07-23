<?php

namespace App\Http\Resources\Administration;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => (bool) $this->is_active,
            'roles' => $this->whenLoaded(
                'roles',
                function () {
                    return $this->roles
                        ->map(function ($role) {
                            return [
                                'id' => $role->id,
                                'name' => $role->name,
                            ];
                        })
                        ->values();
                }
            ),

            'role_names' => $this->whenLoaded(
                'roles',
                function () {
                    return $this->roles
                        ->pluck('name')
                        ->values();
                }
            ),

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
