<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        $clients = Client::query()
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->search, function ($query, $search) {
                $query->where('client_code', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json($clients);
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
        return response()->json([
            'data' => $client,
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
