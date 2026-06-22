<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Exports\CollectionReportExport;
use App\Models\Collection as CollectionModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Barryvdh\DomPDF\Facade\Pdf;


class CollectionReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $this->filteredQuery($request);

        $summaryQuery = clone $query;

        $postedCount = (clone $summaryQuery)
            ->where('status', 'posted')
            ->count();

        $voidedCount = (clone $summaryQuery)
            ->where('status', 'voided')
            ->count();

        $grossCollections = (clone $summaryQuery)
            ->where('status', 'posted')
            ->sum('amount_paid');

        $voidedAmount = (clone $summaryQuery)
            ->where('status', 'voided')
            ->sum('amount_paid');

        $collections = $query
            ->latest('payment_date')
            ->latest('id')
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'summary' => [
                'posted_count' => $postedCount,
                'voided_count' => $voidedCount,
                'gross_collections' => $grossCollections,
                'voided_amount' => $voidedAmount,
                'net_collections' => $grossCollections,
            ],
            'collections' => $collections,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $filename = 'collections-report-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(
            new CollectionReportExport($request->all()),
            $filename
        );
    }
    public function exportPdf(Request $request)
    {
        $query = $this->filteredQuery($request);

        $collections = $query
            ->latest('payment_date')
            ->latest('id')
            ->get();

        $grossCollections = $collections
            ->where('status', 'posted')
            ->sum('amount_paid');

        $voidedAmount = $collections
            ->where('status', 'voided')
            ->sum('amount_paid');

        $pdf = Pdf::loadView('pdf.collection-report', [
            'collections' => $collections,
            'summary' => [
                'posted_count' => $collections->where('status', 'posted')->count(),
                'voided_count' => $collections->where('status', 'voided')->count(),
                'gross_collections' => $grossCollections,
                'voided_amount' => $voidedAmount,
                'net_collections' => $grossCollections,
            ],
            'filters' => $request->all(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download(
            'collections-report-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }


    private function filteredQuery(Request $request)
    {
        $query = CollectionModel::query()
            ->with([
                'sale.client',
                'sale.lot.project',
                'paymentSchedule',
                'voidedByUser',
            ]);

        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('collection_no', 'like', "%{$search}%")
                    ->orWhere('official_receipt_no', 'like', "%{$search}%")
                    ->orWhereHas('sale', function ($q) use ($search) {
                        $q->where('sale_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.client', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('client_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.lot.project', function ($q) use ($search) {
                        $q->where('project_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.lot', function ($q) use ($search) {
                        $q->where('lot_no', 'like', "%{$search}%")
                            ->orWhere('lot_code', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }
}
