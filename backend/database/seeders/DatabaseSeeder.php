<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            PropertyTypeSeeder::class,
            BantogIndustrialSeeder::class,
            ClientAgentSeeder::class,
            UserSeeder::class,
        ]);
    }
}
