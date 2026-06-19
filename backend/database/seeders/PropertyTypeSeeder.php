<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PropertyType;

class PropertyTypeSeeder extends Seeder
{
    public function run(): void
    {
        PropertyType::insert([

            /*
            |--------------------------------------------------------------------------
            | LOTS
            |--------------------------------------------------------------------------
            */

            [
                'code' => 'RESIDENTIAL_LOT',
                'name' => 'Residential Lot',
                'is_active' => true,
            ],

            [
                'code' => 'COMMERCIAL_LOT',
                'name' => 'Commercial Lot',
                'is_active' => true,
            ],

            [
                'code' => 'INDUSTRIAL_LOT',
                'name' => 'Industrial Lot',
                'is_active' => true,
            ],

            [
                'code' => 'FARM_LOT',
                'name' => 'Farm Lot',
                'is_active' => true,
            ],

            [
                'code' => 'AGRICULTURAL_LOT',
                'name' => 'Agricultural Lot',
                'is_active' => true,
            ],

            [
                'code' => 'MEMORIAL_LOT',
                'name' => 'Memorial Lot',
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | HOUSE & LOT
            |--------------------------------------------------------------------------
            */

            [
                'code' => 'HOUSE_AND_LOT',
                'name' => 'House and Lot',
                'is_active' => true,
            ],

            [
                'code' => 'TOWNHOUSE',
                'name' => 'Townhouse',
                'is_active' => true,
            ],

            [
                'code' => 'DUPLEX',
                'name' => 'Duplex Unit',
                'is_active' => true,
            ],

            [
                'code' => 'ROW_HOUSE',
                'name' => 'Row House',
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | CONDOMINIUMS
            |--------------------------------------------------------------------------
            */

            [
                'code' => 'CONDOMINIUM_UNIT',
                'name' => 'Condominium Unit',
                'is_active' => true,
            ],

            [
                'code' => 'PENTHOUSE_UNIT',
                'name' => 'Penthouse Unit',
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | COMMERCIAL
            |--------------------------------------------------------------------------
            */

            [
                'code' => 'COMMERCIAL_SPACE',
                'name' => 'Commercial Space',
                'is_active' => true,
            ],

            [
                'code' => 'OFFICE_SPACE',
                'name' => 'Office Space',
                'is_active' => true,
            ],

            [
                'code' => 'RETAIL_SPACE',
                'name' => 'Retail Space',
                'is_active' => true,
            ],

            [
                'code' => 'SHOPPING_UNIT',
                'name' => 'Shopping Mall Unit',
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | INDUSTRIAL
            |--------------------------------------------------------------------------
            */

            [
                'code' => 'WAREHOUSE',
                'name' => 'Warehouse',
                'is_active' => true,
            ],

            [
                'code' => 'FACTORY_BUILDING',
                'name' => 'Factory Building',
                'is_active' => true,
            ],

            [
                'code' => 'INDUSTRIAL_BUILDING',
                'name' => 'Industrial Building',
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | SPECIAL PROPERTIES
            |--------------------------------------------------------------------------
            */

            [
                'code' => 'RESORT_PROPERTY',
                'name' => 'Resort Property',
                'is_active' => true,
            ],

            [
                'code' => 'HOTEL_PROPERTY',
                'name' => 'Hotel Property',
                'is_active' => true,
            ],

            [
                'code' => 'APARTMENT_BUILDING',
                'name' => 'Apartment Building',
                'is_active' => true,
            ],

            [
                'code' => 'DORMITORY',
                'name' => 'Dormitory',
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | LAND DEVELOPMENT PROJECTS
            |--------------------------------------------------------------------------
            */

            [
                'code' => 'SUBDIVISION_PROJECT',
                'name' => 'Subdivision Project',
                'is_active' => true,
            ],

            [
                'code' => 'MIXED_USE_PROPERTY',
                'name' => 'Mixed-Use Property',
                'is_active' => true,
            ],

            [
                'code' => 'OTHER',
                'name' => 'Other Property Type',
                'is_active' => true,
            ],

        ]);
    }
}
