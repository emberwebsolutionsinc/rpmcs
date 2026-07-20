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

            'email_verified_at' =>
                $this->email_verified_at?->toISOString(),

            'last_login_at' =>
                $this->last_login_at?->toISOString(),

            'created_at' =>
                $this->created_at?->toISOString(),

            'updated_at' =>
                $this->updated_at?->toISOString(),

            'roles' => $this->whenLoaded(
                'roles',
                fn () => $this->roles
                    ->map(
                        fn ($role) => [
                            'id' => $role->id,
                            'name' => $role->name,
                            'guard_name' =>
                                $role->guard_name,
                        ]
                    )
                    ->values()
            ),

            'role_names' => $this->whenLoaded(
                'roles',
                fn () => $this->roles
                    ->pluck('name')
                    ->values()
            ),

            'is_super_admin' => $this->whenLoaded(
                'roles',
                fn () => $this->roles
                    ->contains('name', 'super-admin')
            ),

            'can_be_deleted' => $this->whenLoaded(
                'roles',
                function () use ($request) {
                    $isCurrentUser =
                        $request->user()?->id ===
                        $this->id;

                    $isSuperAdmin =
                        $this->roles->contains(
                            'name',
                            'super-admin'
                        );

                    return !$isCurrentUser &&
                        !$isSuperAdmin;
                }
            ),
        ];
    }
}
