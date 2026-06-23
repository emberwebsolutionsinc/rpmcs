<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Exports\SaleReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SaleReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $this->filteredQuery($request);

        $summaryBaseQuery = clone $query;

        $summary = [
            'total_sales' => (clone $summaryBaseQuery)->count(),

            'active_sales' => (clone $summaryBaseQuery)
                ->where('status', 'active')
                ->count(),

            'fully_paid_sales' => (clone $summaryBaseQuery)
                ->where('status', 'fully_paid')
                ->count(),

            'cancelled_sales' => (clone $summaryBaseQuery)
                ->where('status', 'cancelled')
                ->count(),

            'total_contract_price' => (clone $summaryBaseQuery)
                ->sum('contract_price'),

            'total_down_payment' => (clone $summaryBaseQuery)
                ->sum('downpayment'),

            'total_balance' => (clone $summaryBaseQuery)
                ->sum('balance'),
        ];

        $sales = $query
            ->latest('sale_date')
            ->latest('id')
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'summary' => $summary,
            'sales' => $sales,
            'by_project' => $this->salesByProject($request),
            'by_agent' => $this->salesByAgent($request),
        ]);
    }

    private function filteredQuery(Request $request)
    {
        $query = Sale::query()
            ->with([
                'reservation',
                'client',
                'agent',
                'lot.project',
                'lot.phase',
                'lot.block',
            ]);

        if ($request->filled('from_date')) {
            $query->whereDate('sale_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('sale_date', '<=', $request->to_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('project_id')) {
            $projectId = $request->project_id;

            $query->whereHas('lot.project', function ($query) use ($projectId) {
                $query->where('id', $projectId);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($query) use ($search) {
                $query->where('sale_no', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('client_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('agent', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('agent_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('lot', function ($query) use ($search) {
                        $query->where('lot_no', 'like', "%{$search}%")
                            ->orWhere('lot_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('lot.project', function ($query) use ($search) {
                        $query->where('project_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    private function salesByProject(Request $request): array
    {
        $query = $this->filteredQuery($request)
            ->join('lots', 'sales.lot_id', '=', 'lots.id')
            ->join('property_projects', 'lots.property_project_id', '=', 'property_projects.id')
            ->select([
                'property_projects.id as project_id',
                'property_projects.project_name as project_name',
                DB::raw('COUNT(sales.id) as total_sales'),
                DB::raw('SUM(sales.contract_price) as total_contract_price'),
                DB::raw('SUM(sales.balance) as total_balance'),
            ])
            ->groupBy([
                'property_projects.id',
                'property_projects.project_name',
            ])
            ->orderByDesc('total_contract_price');

        return $query->get()->toArray();
    }

    private function salesByAgent(Request $request): array
    {
        $query = $this->filteredQuery($request)
            ->leftJoin('agents', 'sales.agent_id', '=', 'agents.id')
            ->select([
                'agents.id as agent_id',
                DB::raw("CONCAT_WS(' ', agents.first_name, agents.middle_name, agents.last_name) as agent_name"),
                DB::raw('COUNT(sales.id) as total_sales'),
                DB::raw('SUM(sales.contract_price) as total_contract_price'),
                DB::raw('SUM(sales.balance) as total_balance'),
            ])
            ->groupBy([
                'agents.id',
                'agents.first_name',
                'agents.middle_name',
                'agents.last_name',
            ])
            ->orderByDesc('total_contract_price');

        return $query->get()->toArray();
    }


    public function exportExcel(Request $request)
{
    return Excel::download(
        new SaleReportExport($request->all()),
        'sales-report.xlsx'
    );
}

public function exportPdf(Request $request)
{
    $query = Sale::query()
        ->with([
            'client',
            'property',
            'agent',
        ]);

    if ($request->filled('date_from')) {
        $query->whereDate('sale_date', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('sale_date', '<=', $request->date_to);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('agent_id')) {
        $query->where('agent_id', $request->agent_id);
    }

    $sales = $query->latest('sale_date')->get();

    $summary = [
        'total_sales' => $sales->count(),
        'total_contract_price' => $sales->sum('total_contract_price'),
        'total_reservation_fee' => $sales->sum('reservation_fee'),
        'total_downpayment' => $sales->sum('downpayment'),
        'total_commission' => $sales->sum('commission_amount'),
    ];

    $pdf = Pdf::loadView('pdf.sale-report', [
        'sales' => $sales,
        'summary' => $summary,
        'filters' => $request->all(),
    ])->setPaper('a4', 'landscape');

    return $pdf->download('sales-report.pdf');
}


}
