<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $saleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $sales = Sale::query()
            ->with([
                'reservation',
                'client',
                'lot.project',
                'lot.phase',
                'lot.block',
                'agent',
            ])

            ->when(
                $request->search,
                function ($query, $search) {

                    $query->where(
                        function ($query) use ($search) {

                            $query->where(
                                'sale_no',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhereHas(
                                'client',
                                function ($clientQuery) use ($search) {

                                    $clientQuery
                                        ->where(
                                            'first_name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'last_name',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            )

                            ->orWhereHas(
                                'lot',
                                function ($lotQuery) use ($search) {

                                    $lotQuery
                                        ->where(
                                            'lot_no',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'lot_code',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            );
                        }
                    );
                }
            )

            ->when(
                $request->status,
                fn ($query, $status) =>
                    $query->where('status', $status)
            )

            ->latest()
            ->paginate(
                $request->per_page ?? 10
            );

        return response()->json($sales);
    }

    public function store(
        StoreSaleRequest $request
    ): JsonResponse {

        $sale = $this->saleService->create(
            $request->validated()
        );

        return response()->json([
            'message' => 'Sale created successfully.',
            'data' => $sale,
        ], 201);
    }

    public function show(
        Sale $sale
    ): JsonResponse {

        $sale->load([
            'reservation',
            'client',
            'lot.project',
            'lot.phase',
            'lot.block',
            'agent',
        ]);

        return response()->json([
            'data' => $sale,
        ]);
    }

    public function update(
        UpdateSaleRequest $request,
        Sale $sale
    ): JsonResponse {

        $sale = $this->saleService->update(
            $sale,
            $request->validated()
        );

        return response()->json([
            'message' => 'Sale updated successfully.',
            'data' => $sale,
        ]);
    }

    public function cancel(
        Sale $sale
    ): JsonResponse {

        $sale = $this->saleService->cancel(
            $sale
        );

        return response()->json([
            'message' => 'Sale cancelled successfully.',
            'data' => $sale,
        ]);
    }

    public function destroy(
        Sale $sale
    ): JsonResponse {

        $this->saleService->delete(
            $sale
        );

        return response()->json([
            'message' => 'Sale deleted successfully.',
        ]);
    }

    public function summary(): JsonResponse
{
    $totalSales = Sale::query()->count();

    $activeSales = Sale::query()
        ->where('status', 'active')
        ->count();

    $cancelledSales = Sale::query()
        ->where('status', 'cancelled')
        ->count();

    $fullyPaidSales = Sale::query()
        ->where('status', 'fully_paid')
        ->count();

    $totalContractPrice = Sale::query()
        ->where('status', '!=', 'cancelled')
        ->sum('contract_price');

    $totalBalance = Sale::query()
        ->where('status', '!=', 'cancelled')
        ->sum('balance');

    return response()->json([
        'data' => [
            'total_sales' => $totalSales,
            'active_sales' => $activeSales,
            'cancelled_sales' => $cancelledSales,
            'fully_paid_sales' => $fullyPaidSales,
            'total_contract_price' => $totalContractPrice,
            'total_balance' => $totalBalance,
        ],
    ]);
}
}
