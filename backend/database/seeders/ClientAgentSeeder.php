<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Agent;

class ClientAgentSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['Juan', 'Santos', 'Dela Cruz', '09171234501', 'juan.delacruz@example.com', 'La Paz, Tarlac'],
            ['Maria', 'Reyes', 'Garcia', '09171234502', 'maria.garcia@example.com', 'Tarlac City, Tarlac'],
            ['Pedro', 'Lopez', 'Mendoza', '09171234503', 'pedro.mendoza@example.com', 'Capas, Tarlac'],
            ['Ana', 'Torres', 'Villanueva', '09171234504', 'ana.villanueva@example.com', 'Concepcion, Tarlac'],
            ['Jose', 'Ramos', 'Castro', '09171234505', 'jose.castro@example.com', 'Bamban, Tarlac'],
            ['Carla', 'Diaz', 'Aquino', '09171234506', 'carla.aquino@example.com', 'Victoria, Tarlac'],
            ['Michael', 'Flores', 'Navarro', '09171234507', 'michael.navarro@example.com', 'Paniqui, Tarlac'],
            ['Patricia', 'Perez', 'Salazar', '09171234508', 'patricia.salazar@example.com', 'Gerona, Tarlac'],
            ['Dennis', 'Gonzales', 'Morales', '09171234509', 'dennis.morales@example.com', 'Moncada, Tarlac'],
            ['Michelle', 'Rivera', 'Santiago', '09171234510', 'michelle.santiago@example.com', 'Camiling, Tarlac'],
        ];

        foreach ($clients as $index => $client) {
            Client::firstOrCreate(
                [
                    'client_code' => 'CLI-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                ],
                [
                    'first_name' => $client[0],
                    'middle_name' => $client[1],
                    'last_name' => $client[2],
                    'suffix' => null,
                    'birthdate' => now()->subYears(rand(25, 55))->format('Y-m-d'),
                    'civil_status' => $index % 2 === 0 ? 'Single' : 'Married',
                    'nationality' => 'Filipino',
                    'tin' => null,
                    'email' => $client[4],
                    'contact_number' => $client[3],
                    'telephone_number' => null,
                    'address' => $client[5],
                    'status' => 'active',
                ]
            );
        }

        $agents = [
            ['Mark', 'Glenn', 'Manugue', '09181234501', 'mark.manugue@example.com', 'Capas, Tarlac', 'main_agent'],
            ['Jennifer', 'Mae', 'Cruz', '09181234502', 'jennifer.cruz@example.com', 'La Paz, Tarlac', 'main_agent'],
            ['Ronald', 'James', 'Santos', '09181234503', 'ronald.santos@example.com', 'Tarlac City, Tarlac', 'sub_agent'],
            ['Kristine', 'Joy', 'Torres', '09181234504', 'kristine.torres@example.com', 'Concepcion, Tarlac', 'sub_agent'],
            ['Alvin', 'Paul', 'Ramos', '09181234505', 'alvin.ramos@example.com', 'Bamban, Tarlac', 'sub_agent'],
            ['Sharon', 'Lee', 'Garcia', '09181234506', 'sharon.garcia@example.com', 'Victoria, Tarlac', 'main_agent'],
            ['Patrick', 'John', 'Reyes', '09181234507', 'patrick.reyes@example.com', 'Paniqui, Tarlac', 'sub_agent'],
            ['Angela', 'Marie', 'Mendoza', '09181234508', 'angela.mendoza@example.com', 'Gerona, Tarlac', 'sub_agent'],
            ['Kevin', 'Mark', 'Flores', '09181234509', 'kevin.flores@example.com', 'Moncada, Tarlac', 'main_agent'],
            ['Sophia', 'Anne', 'Rivera', '09181234510', 'sophia.rivera@example.com', 'Camiling, Tarlac', 'sub_agent'],
        ];

        foreach ($agents as $index => $agent) {
            Agent::firstOrCreate(
                [
                    'agent_code' => 'AGT-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                ],
                [
                    'parent_agent_id' => null,
                    'first_name' => $agent[0],
                    'middle_name' => $agent[1],
                    'last_name' => $agent[2],
                    'suffix' => null,
                    'contact_number' => $agent[3],
                    'email' => $agent[4],
                    'address' => $agent[5],
                    'license_number' => 'PRC-REB-' . str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                    'default_commission_rate' => $agent[6] === 'main_agent' ? 5.00 : 3.00,
                    'agent_type' => $agent[6],
                    'status' => 'active',
                ]
            );
        }
    }
}
