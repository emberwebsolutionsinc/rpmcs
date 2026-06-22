<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollectionRequest;
use App\Http\Requests\VoidCollectionRequest;
use App\Models\Collection;
use App\Models\Sale;
use App\Services\CollectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CollectionController extends Controller
{
    public function __construct(
        protected CollectionService $collectionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $collections = Collection::query()
            ->with([
                'sale.client',
                'sale.lot.project',
                'paymentSchedule',
                'voidedBy',
            ])
            ->when($request->sale_id, function ($query, $saleId) {
                $query->where('sale_id', $saleId);
            })
            ->when($request->payment_schedule_id, function ($query, $scheduleId) {
                $query->where('payment_schedule_id', $scheduleId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json($collections);
    }

    public function store(StoreCollectionRequest $request): JsonResponse
    {
        $collection = $this->collectionService->create(
            $request->validated()
        );

        return response()->json([
            'message' => 'Payment posted successfully.',
            'data' => $collection,
        ], 201);
    }

    public function show(Collection $collection): JsonResponse
    {
        $collection->load([
            'sale.client',
            'sale.lot.project',
            'paymentSchedule',
            'voidedBy',
        ]);

        return response()->json([
            'data' => $collection,
        ]);
    }

    public function void(
        VoidCollectionRequest $request,
        Collection $collection
    ): JsonResponse {
        $collection = $this->collectionService->void(
            $collection,
            $request->validated(),
            $request->user()?->id
        );

        return response()->json([
            'message' => 'Collection voided successfully.',
            'data' => $collection,
        ]);
    }

    public function summary(): JsonResponse
    {
        $today = Carbon::today();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return response()->json([
            'data' => [
                'total_collected' => Collection::query()
                    ->where('status', 'posted')
                    ->sum('amount_paid'),

                'today_collected' => Collection::query()
                    ->where('status', 'posted')
                    ->whereDate('payment_date', $today)
                    ->sum('amount_paid'),

                'monthly_collected' => Collection::query()
                    ->where('status', 'posted')
                    ->whereBetween('payment_date', [
                        $startOfMonth,
                        $endOfMonth,
                    ])
                    ->sum('amount_paid'),

                'outstanding_balance' => Sale::query()
                    ->where('status', '!=', 'cancelled')
                    ->sum('balance'),
            ],
        ]);
    }
}
