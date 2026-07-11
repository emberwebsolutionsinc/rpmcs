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
            'documents.uploadedBy',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Reusable Collection Amount Resolver
        |--------------------------------------------------------------------------
        |
        | This supports the possible amount column names currently used in RPMCS.
        | The first non-null value will be used.
        |
        */

        $getCollectionAmount = function ($collection): float {
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

        /*
        |--------------------------------------------------------------------------
        | Sales Data
        |--------------------------------------------------------------------------
        */

        $sales = $client->sales->map(
            function ($sale) use ($getCollectionAmount) {
                $contractPrice = (float) (
                    $sale->contract_price ?? 0
                );

                $downpayment = (float) (
                    $sale->downpayment ?? 0
                );

                $collectionsTotal = (float) $sale
                    ->collections
                    ->sum(
                        function ($collection) use ($getCollectionAmount) {
                            return $getCollectionAmount($collection);
                        }
                    );

                $totalCollected =
                    $downpayment + $collectionsTotal;

                $balance = max(
                    $contractPrice - $totalCollected,
                    0
                );

                $commissionPaid = (float) $sale
                    ->agentCommissionPayments
                    ->sum('amount');

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
                    'total_collected' => $totalCollected,
                    'balance' => $balance,

                    'commission_paid' => $commissionPaid,

                    'client' => $sale->client,
                    'agent' => $sale->agent,
                    'lot' => $sale->lot,

                    'project' =>
                        $sale->lot?->block?->phase?->project,

                    'phase' =>
                        $sale->lot?->block?->phase,

                    'block' =>
                        $sale->lot?->block,
                ];
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Collection Records
        |--------------------------------------------------------------------------
        */

        $collections = $client->sales
            ->flatMap(
                function ($sale) use ($getCollectionAmount) {
                    return $sale->collections->map(
                        function ($collection) use (
                            $sale,
                            $getCollectionAmount
                        ) {
                            $amountReceived =
                                $getCollectionAmount($collection);

                            return [
                                'id' => $collection->id,
                                'sale_id' => $sale->id,
                                'sale_no' => $sale->sale_no,

                                'amount' => $amountReceived,
                                'amount_received' => $amountReceived,

                                'reference_no' =>
                                    $collection->reference_no
                                    ?? null,

                                'or_no' =>
                                    $collection->or_no
                                    ?? null,

                                'payment_date' =>
                                    $collection->payment_date
                                    ?? null,

                                'collection_date' =>
                                    $collection->collection_date
                                    ?? null,

                                'created_at' =>
                                    $collection->created_at,

                                'remarks' =>
                                    $collection->remarks
                                    ?? null,

                                'sale' => [
                                    'id' => $sale->id,
                                    'sale_no' => $sale->sale_no,
                                ],
                            ];
                        }
                    );
                }
            )
            ->sortByDesc(
                function ($collection) {
                    return $collection['payment_date']
                        ?? $collection['collection_date']
                        ?? $collection['created_at'];
                }
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total_sales' =>
                $sales->count(),

            'total_contract_price' =>
                $sales->sum('contract_price'),

            'total_downpayment' =>
                $sales->sum('downpayment'),

            'total_collections' =>
                $sales->sum('collections_total'),

            'total_collected' =>
                $sales->sum('total_collection'),

            'total_balance' =>
                $sales->sum('balance'),

            'documents_count' =>
                $client->documents->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Dynamic Timeline
        |--------------------------------------------------------------------------
        */

        $timeline = collect();

        /*
        * Client created event
        */
        if ($client->created_at) {
            $timeline->push([
                'id' =>
                    'client-created-' . $client->id,

                'type' =>
                    'client_created',

                'title' =>
                    'Client profile created',

                'description' =>
                    'The client profile was created in the system.',

                'date' =>
                    $client->created_at,

                'created_at' =>
                    $client->created_at,

                'icon' =>
                    'user-plus',

                'color' =>
                    'blue',

                'amount' =>
                    null,

                'metadata' => [
                    'client_id' => $client->id,
                ],
            ]);
        }

        /*
        * Client updated event
        */
        if (
            $client->updated_at
            && $client->created_at
            && !$client->updated_at->equalTo(
                $client->created_at
            )
        ) {
            $timeline->push([
                'id' =>
                    'client-updated-' . $client->id,

                'type' =>
                    'client_updated',

                'title' =>
                    'Client profile updated',

                'description' =>
                    'The client profile information was updated.',

                'date' =>
                    $client->updated_at,

                'created_at' =>
                    $client->updated_at,

                'icon' =>
                    'user-cog',

                'color' =>
                    'slate',

                'amount' =>
                    null,

                'metadata' => [
                    'client_id' => $client->id,
                ],
            ]);
        }

        /*
        * Sale, downpayment, and collection events
        */
        foreach ($client->sales as $sale) {
            $projectName =
                $sale->lot?->block?->phase?->project?->project_name
                ?? 'Unknown Project';

            $phaseName =
                $sale->lot?->block?->phase?->phase_name
                ?? '—';

            $blockNumber =
                $sale->lot?->block?->block_no
                ?? '—';

            $lotNumber =
                $sale->lot?->lot_no
                ?? '—';

            /*
            * Sale created
            */
            $timeline->push([
                'id' =>
                    'sale-created-' . $sale->id,

                'type' =>
                    'sale_created',

                'title' =>
                    'Sale created',

                'description' =>
                    "Sale {$sale->sale_no} was created for "
                    . "{$projectName}, Phase {$phaseName}, "
                    . "Block {$blockNumber}, Lot {$lotNumber}.",

                'date' =>
                    $sale->sale_date
                    ?? $sale->created_at,

                'created_at' =>
                    $sale->created_at,

                'icon' =>
                    'house',

                'color' =>
                    'emerald',

                'amount' =>
                    (float) ($sale->contract_price ?? 0),

                'metadata' => [
                    'sale_id' => $sale->id,
                    'sale_no' => $sale->sale_no,
                    'project_name' => $projectName,
                    'phase_name' => $phaseName,
                    'block_no' => $blockNumber,
                    'lot_no' => $lotNumber,
                ],
            ]);

            /*
            * Downpayment recorded
            */
            if ((float) ($sale->downpayment ?? 0) > 0) {
                $timeline->push([
                    'id' =>
                        'downpayment-' . $sale->id,

                    'type' =>
                        'downpayment_received',

                    'title' =>
                        'Downpayment recorded',

                    'description' =>
                        "A downpayment was recorded for sale {$sale->sale_no}.",

                    'date' =>
                        $sale->sale_date
                        ?? $sale->created_at,

                    'created_at' =>
                        $sale->created_at,

                    'icon' =>
                        'banknote',

                    'color' =>
                        'purple',

                    'amount' =>
                        (float) $sale->downpayment,

                    'metadata' => [
                        'sale_id' => $sale->id,
                        'sale_no' => $sale->sale_no,
                    ],
                ]);
            }

            /*
            * Monthly collection records
            */
            foreach ($sale->collections as $collection) {
                $amountReceived =
                    $getCollectionAmount($collection);

                $paymentDate =
                    $collection->payment_date
                    ?? $collection->collection_date
                    ?? $collection->created_at;

                $reference =
                    $collection->reference_no
                    ?? $collection->or_no
                    ?? null;

                $description =
                    "A monthly installment was received for sale "
                    . "{$sale->sale_no}.";

                if ($reference) {
                    $description .=
                        " Reference: {$reference}.";
                }

                $timeline->push([
                    'id' =>
                        'collection-' . $collection->id,

                    'type' =>
                        'payment_received',

                    'title' =>
                        'Monthly installment received',

                    'description' =>
                        $description,

                    'date' =>
                        $paymentDate,

                    'created_at' =>
                        $collection->created_at,

                    'icon' =>
                        'receipt-text',

                    'color' =>
                        'green',

                    'amount' =>
                        $amountReceived,

                    'metadata' => [
                        'collection_id' => $collection->id,
                        'sale_id' => $sale->id,
                        'sale_no' => $sale->sale_no,
                        'reference_no' => $reference,
                    ],
                ]);
            }
        }

        /*
        * Document upload events
        */
        foreach ($client->documents as $document) {
            $uploaderName = null;

            if ($document->uploadedBy) {
                $uploaderName =
                    $document->uploadedBy->name
                    ?? trim(
                        ($document->uploadedBy->first_name ?? '')
                        . ' '
                        . ($document->uploadedBy->last_name ?? '')
                    );
            }

            if (!$uploaderName) {
                $uploaderName = 'System';
            }

            $timeline->push([
                'id' =>
                    'document-uploaded-' . $document->id,

                'type' =>
                    'document_uploaded',

                'title' =>
                    'Document uploaded',

                'description' =>
                    "{$document->document_name} was uploaded by "
                    . "{$uploaderName}.",

                'date' =>
                    $document->created_at,

                'created_at' =>
                    $document->created_at,

                'icon' =>
                    'file-up',

                'color' =>
                    'amber',

                'amount' =>
                    null,

                'metadata' => [
                    'document_id' =>
                        $document->id,

                    'document_name' =>
                        $document->document_name,

                    'document_type' =>
                        $document->document_type,

                    'file_name' =>
                        $document->file_name,

                    'uploaded_by' =>
                        $uploaderName,
                ],
            ]);
        }

        /*
        * Sort newest activity first
        */
        $timeline = $timeline
            ->sortByDesc(
                function ($event) {
                    return $event['date'];
                }
            )
            ->values();

        return response()->json([
            'data' => $client,
            'summary' => $summary,
            'sales' => $sales,
            'collections' => $collections,
            'documents' => $client->documents,

            /*
            * Return both keys for compatibility.
            */
            'activities' => $timeline,
            'timeline' => $timeline,
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
