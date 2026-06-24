<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentCommissionPayment;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentCommissionPaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AgentCommissionPayment::query()
            ->with([
                'agent',
                'sale.client',
                'sale.lot.project',
                'createdBy',
            ])
            ->latest('payment_date')
            ->latest('id');

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('sale_id')) {
            $query->where('sale_id', $request->sale_id);
        }

        return response()->json([
            'data' => $query->paginate(
                $request->integer('per_page', 10)
            ),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
       $validated = $request->validate([
        'sale_id' => [
            'required',
            'exists:sales,id',
        ],
        'payment_date' => [
            'required',
            'date',
        ],
        'amount' => [
            'required',
            'numeric',
            'min:1',
        ],
        'payment_method' => [
            'required',
            'string',
            'max:100',
        ],
        'reference_no' => [
            'required',
            'string',
            'max:100',
        ],
        'remarks' => [
            'nullable',
            'string',
        ],
    ]);

        $sale = Sale::query()
            ->with('agent')
            ->findOrFail($validated['sale_id']);

        if (! $sale->agent_id) {
           $commissionRate = (float) ($sale->agent?->default_commission_rate ?? 0);

            $commissionEarned = (float) $sale->contract_price * ($commissionRate / 100);

            $commissionPaid = AgentCommissionPayment::query()
                ->where('sale_id', $sale->id)
                ->sum('amount');

            $remainingCommission = max(
                $commissionEarned - $commissionPaid,
                0
            );

            if ((float) $validated['amount'] > $remainingCommission) {
                return response()->json([
                    'message' => 'Payment amount cannot exceed remaining commission balance.',
                    'errors' => [
                        'amount' => [
                            'Maximum allowed payment is ₱' . number_format($remainingCommission, 2),
                        ],
                    ],
                ], 422);
            }

            return response()->json([
                'message' => 'This sale has no assigned agent.',
            ], 422);
        }

        $payment = AgentCommissionPayment::create([
            'agent_id' => $sale->agent_id,
            'sale_id' => $sale->id,
            'payment_date' => $validated['payment_date'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'] ?? null,
            'reference_no' => $validated['reference_no'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'Commission payment recorded successfully.',
            'data' => $payment->load([
                'agent',
                'sale.client',
                'sale.lot.project',
                'createdBy',
            ]),
        ], 201);
    }

    public function destroy(
        Request $request,
        AgentCommissionPayment $agentCommissionPayment
    ): JsonResponse {
        $validated = $request->validate([
            'delete_reason' => [
                'required',
                'string',
                'min:5',
            ],
        ]);

        $agentCommissionPayment->update([
            'delete_reason' => $validated['delete_reason'],
            'deleted_by' => $request->user()?->id,
        ]);

        $agentCommissionPayment->delete();

        return response()->json([
            'message' => 'Commission payment deleted successfully.',
        ]);
    }
}
