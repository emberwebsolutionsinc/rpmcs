<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\PropertyProject;
use App\Models\ProjectPhase;
use App\Models\ProjectBlock;
use App\Models\Lot;
use App\Models\PropertyType;

class BantogIndustrialSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Property Type
        |--------------------------------------------------------------------------
        */

        $propertyType = PropertyType::firstOrCreate(
            [
                'code' => 'INDUSTRIAL',
            ],
            [
                'name' => 'Industrial Lot',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Project
        |--------------------------------------------------------------------------
        */

        $project = PropertyProject::firstOrCreate(
            [
                'project_code' => 'BANTOG-IP',
            ],
            [
                'project_name' => 'BANTOG INDUSTRIAL PARK',

                'developer_name' => 'BANTOG DEVELOPMENT CORPORATION',

                'street' => 'Bantog',
                'barangay' => 'Bantog',
                'municipality' => 'La Paz',
                'province' => 'Tarlac',

                'status' => 'active',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Phase
        |--------------------------------------------------------------------------
        */

        $phase = ProjectPhase::firstOrCreate(
            [
                'property_project_id' => $project->id,
                'phase_code' => 'PH1',
            ],
            [
                'phase_name' => 'Phase 1',
                'description' => 'Main industrial phase for BANTOG Industrial Lots',
                'status' => 'active',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Block
        |--------------------------------------------------------------------------
        */

        $block = ProjectBlock::firstOrCreate(
            [
                'property_project_id' => $project->id,
                'project_phase_id' => $phase->id,
                'block_no' => 'Block A',
            ],
            [
                'description' => 'Main block for BANTOG Industrial Lots',
                'status' => 'active',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Lots 1-100
        |--------------------------------------------------------------------------
        */

        for ($i = 1; $i <= 100; $i++) {

        Lot::firstOrCreate(
            [
                'lot_code' => 'BANTOG-IND-' . str_pad($i, 3, '0', STR_PAD_LEFT),
            ],
            [
                'property_project_id' => $project->id,
                'project_phase_id' => $phase->id,
                'project_block_id' => $block->id,
                'property_type_id' => $propertyType->id,

                'lot_no' => 'Lot ' . $i,
                'title_no' => null,

                'lot_area' => 1000,
                'price_per_sqm' => 4500,
                'selling_price' => 4500000,

                'corner_lot' => false,
                'road_lot' => false,

                'status' => 'available',
                'remarks' => 'Industrial Lot located in Bantog, La Paz, Tarlac.',
            ]
        );
        }
    }
}
