<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService
    ) {}

public function index(Request $request): JsonResponse
{
    $reservations = Reservation::query()
        ->with([
            'client',
            'agent',
            'lot.project',
            'lot.phase',
            'lot.block',
        ])
        ->when($request->search, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('reservation_no', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('client_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('lot', function ($query) use ($search) {
                        $query->where('lot_no', 'like', "%{$search}%")
                            ->orWhere('lot_code', 'like', "%{$search}%");
                    });
            });
        })
        ->when($request->status, function ($query, $status) {
            $query->where('status', $status);
        })
        ->latest()
        ->paginate($request->per_page ?? 10);

    return response()->json($reservations);
}

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $reservation = $this->reservationService->create($request->validated());

        return response()->json([
            'message' => 'Reservation created successfully.',
            'data' => $reservation,
        ], 201);
    }

    public function show(Reservation $reservation): JsonResponse
    {
        $reservation->load([
            'client',
            'lot.project',
            'lot.phase',
            'lot.block',
            'agent',
        ]);

        return response()->json([
            'data' => $reservation,
        ]);
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation): JsonResponse
    {
        $reservation = $this->reservationService->update(
            $reservation,
            $request->validated()
        );

        return response()->json([
            'message' => 'Reservation updated successfully.',
            'data' => $reservation,
        ]);
    }

    public function cancel(Reservation $reservation): JsonResponse
    {
        $reservation = $this->reservationService->cancel($reservation);

        return response()->json([
            'message' => 'Reservation cancelled successfully.',
            'data' => $reservation,
        ]);
    }

    public function destroy(Reservation $reservation): JsonResponse
    {
        $this->reservationService->delete($reservation);

        return response()->json([
            'message' => 'Reservation deleted successfully.',
        ]);
    }
}
