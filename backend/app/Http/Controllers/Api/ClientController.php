<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(
        protected ClientService $clientService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Client::query()
            ->withCount('sales')
            ->withSum('sales as total_contract_price', 'contract_price');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('client_code', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $clients = $query
            ->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'data' => $clients->items(),

            'summary' => [
                'total_clients' => Client::count(),

                'active_clients' => Client::where('status', 'active')->count(),

                'clients_with_sales' => Client::whereHas('sales')->count(),

                'total_contract_price' => Sale::sum('contract_price'),
            ],

            'current_page' => $clients->currentPage(),
            'last_page' => $clients->lastPage(),
            'total' => $clients->total(),
        ]);
    }
    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientService->create($request->validated());

        return response()->json([
            'message' => 'Client created successfully.',
            'data' => $client,
        ], 201);
    }

    public function show(Client $client): JsonResponse
    {
        $client->load([
            'sales.client',
            'sales.agent',
            'sales.lot.block.phase.project',
            'sales.collections',
            'sales.agentCommissionPayments',
        ]);

        $getCollectionAmount = function ($collection) {
            return (float) (
                $collection->amount_received
                ?? $collection->amount_paid
                ?? $collection->payment_amount
                ?? $collection->collection_amount
                ?? $collection->paid_amount
                ?? $collection->amount
                ?? 0
            );
        };

        $sales = $client->sales->map(function ($sale) use ($getCollectionAmount) {
            $contractPrice = (float) ($sale->contract_price ?? 0);

            $downpayment = (float) ($sale->downpayment ?? 0);

            $collectionsTotal = (float) $sale->collections->sum(function ($collection) use ($getCollectionAmount) {
                return $getCollectionAmount($collection);
            });

            $totalCollected = $downpayment + $collectionsTotal;

            $balance = max($contractPrice - $totalCollected, 0);

            $commissionPaid = (float) $sale->agentCommissionPayments->sum('amount');

            return [
                'id' => $sale->id,
                'sale_id' => $sale->id,
                'sale_no' => $sale->sale_no,
                'sale_date' => $sale->sale_date,
                'status' => $sale->status,

                'client_id' => $sale->client_id,
                'agent_id' => $sale->agent_id,
                'lot_id' => $sale->lot_id,

                'contract_price' => $contractPrice,
                'downpayment' => $downpayment,

                'collections_total' => $collectionsTotal,
                'total_collection' => $totalCollected,
                'balance' => $balance,

                'commission_paid' => $commissionPaid,

                'client' => $sale->client,
                'agent' => $sale->agent,
                'lot' => $sale->lot,
                'project' => $sale->lot?->block?->phase?->project,
                'phase' => $sale->lot?->block?->phase,
                'block' => $sale->lot?->block,
            ];
        });

        $collections = $client->sales
            ->flatMap(function ($sale) use ($getCollectionAmount) {
                return $sale->collections->map(function ($collection) use ($sale, $getCollectionAmount) {
                    $amountReceived = $getCollectionAmount($collection);

                    return [
                        'id' => $collection->id,
                        'sale_id' => $sale->id,
                        'sale_no' => $sale->sale_no,

                        'amount' => $amountReceived,
                        'amount_received' => $amountReceived,

                        'reference_no' => $collection->reference_no ?? null,
                        'or_no' => $collection->or_no ?? null,
                        'payment_date' => $collection->payment_date ?? null,
                        'collection_date' => $collection->collection_date ?? null,
                        'created_at' => $collection->created_at,
                        'remarks' => $collection->remarks ?? null,

                        'sale' => [
                            'id' => $sale->id,
                            'sale_no' => $sale->sale_no,
                        ],
                    ];
                });
            })
            ->sortByDesc(function ($collection) {
                return $collection['payment_date']
                    ?? $collection['collection_date']
                    ?? $collection['created_at'];
            })
            ->values();

        $summary = [
            'total_sales' => $sales->count(),
            'total_contract_price' => $sales->sum('contract_price'),
            'total_downpayment' => $sales->sum('downpayment'),
            'total_collections' => $sales->sum('collections_total'),
            'total_collected' => $sales->sum('total_collection'),
            'total_balance' => $sales->sum('balance'),
        ];

        return response()->json([
            'data' => $client,
            'summary' => $summary,
            'sales' => $sales,
            'collections' => $collections,
            'documents' => [],
            'activities' => [],
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client = $this->clientService->update($client, $request->validated());

        return response()->json([
            'message' => 'Client updated successfully.',
            'data' => $client,
        ]);
    }

    public function destroy(Client $client): JsonResponse
    {
        $this->clientService->delete($client);

        return response()->json([
            'message' => 'Client deleted successfully.',
        ]);
    }
}
