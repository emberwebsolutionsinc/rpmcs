<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Collection::query()
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

            $query->where(function ($query) use ($search) {
                $query->where('collection_no', 'like', "%{$search}%")
                    ->orWhere('official_receipt_no', 'like', "%{$search}%")
                    ->orWhereHas('sale', function ($query) use ($search) {
                        $query->where('sale_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.client', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('client_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.lot.project', function ($query) use ($search) {
                        $query->where('project_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.lot', function ($query) use ($search) {
                        $query->where('lot_no', 'like', "%{$search}%")
                            ->orWhere('lot_code', 'like', "%{$search}%");
                    });
            });
        }

        $summaryBaseQuery = clone $query;

        $postedCount = (clone $summaryBaseQuery)
            ->where('status', 'posted')
            ->count();

        $voidedCount = (clone $summaryBaseQuery)
            ->where('status', 'voided')
            ->count();

        $grossCollections = (clone $summaryBaseQuery)
            ->where('status', 'posted')
            ->sum('amount_paid');

        $voidedAmount = (clone $summaryBaseQuery)
            ->where('status', 'voided')
            ->sum('amount_paid');

        $summary = [
            'posted_count' => $postedCount,
            'voided_count' => $voidedCount,
            'gross_collections' => $grossCollections,
            'voided_amount' => $voidedAmount,
            'net_collections' => $grossCollections,
        ];

        $collections = $query
            ->latest('payment_date')
            ->latest('id')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'summary' => $summary,
            'collections' => $collections,
        ]);
    }
}
