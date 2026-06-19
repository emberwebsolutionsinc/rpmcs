<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyProject;
use App\Models\ProjectPhase;
use App\Models\ProjectBlock;
use App\Models\Lot;
use App\Models\Client;
use App\Models\Agent;
use App\Models\Reservation;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function summary(): JsonResponse
    {
        return response()->json([
            'data' => [
                'projects' => PropertyProject::count(),
                'phases' => ProjectPhase::count(),
                'blocks' => ProjectBlock::count(),
                'lots' => Lot::count(),

                'available_lots' => Lot::where('status', 'available')->count(),
                'reserved_lots' => Lot::where('status', 'reserved')->count(),
                'sold_lots' => Lot::where('status', 'sold')->count(),

                'clients' => Client::count(),
                'agents' => Agent::count(),

                'reservations' => Reservation::count(),
                'active_reservations' => Reservation::where('status', 'active')->count(),
                'converted_reservations' => Reservation::where('status', 'converted_to_sale')->count(),

                'sales' => Sale::count(),
                'active_sales' => Sale::where('status', 'active')->count(),
                'total_sales_value' => Sale::where('status', '!=', 'cancelled')->sum('contract_price'),
                'total_balance' => Sale::where('status', '!=', 'cancelled')->sum('balance'),
            ],
        ]);
    }
}
