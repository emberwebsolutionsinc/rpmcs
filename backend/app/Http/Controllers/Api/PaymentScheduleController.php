<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeneratePaymentScheduleRequest;
use App\Models\PaymentSchedule;
use App\Services\PaymentScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentScheduleController extends Controller
{
    public function __construct(
        protected PaymentScheduleService $paymentScheduleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $schedules = PaymentSchedule::query()
            ->with([
                'sale.client',
                'sale.lot.project',
                'sale.agent',
            ])
            ->when($request->sale_id, function ($query, $saleId) {
                $query->where('sale_id', $saleId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('due_date')
            ->paginate($request->per_page ?? 10);

        return response()->json($schedules);
    }

    public function generate(GeneratePaymentScheduleRequest $request): JsonResponse
    {
        $result = $this->paymentScheduleService->generate(
            $request->validated()
        );

        return response()->json([
            'message' => 'Payment schedule generated successfully.',
            'data' => $result,
        ], 201);
    }

    public function show(PaymentSchedule $paymentSchedule): JsonResponse
    {
        $paymentSchedule->load([
            'sale.client',
            'sale.lot.project',
            'sale.agent',
        ]);

        return response()->json([
            'data' => $paymentSchedule,
        ]);
    }

    public function destroy(PaymentSchedule $paymentSchedule): JsonResponse
    {
        $paymentSchedule->delete();

        return response()->json([
            'message' => 'Payment schedule deleted successfully.',
        ]);
    }
}
