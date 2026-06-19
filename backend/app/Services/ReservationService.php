<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function create(array $data): Reservation
    {
        return DB::transaction(function () use ($data) {
            $lot = Lot::lockForUpdate()->findOrFail($data['lot_id']);

            if ($lot->status !== 'available') {
                throw ValidationException::withMessages([
                    'lot_id' => 'This lot is not available for reservation.',
                ]);
            }

            $reservation = Reservation::create($data);

            $lot->update([
                'status' => 'reserved',
            ]);

            return $reservation->fresh([
                'client',
                'lot.project',
                'lot.phase',
                'lot.block',
                'agent',
            ]);
        });
    }

    public function update(Reservation $reservation, array $data): Reservation
    {
        return DB::transaction(function () use ($reservation, $data) {
            $oldLotId = $reservation->lot_id;

            if ($oldLotId !== (int) $data['lot_id']) {
                $newLot = Lot::lockForUpdate()->findOrFail($data['lot_id']);

                if ($newLot->status !== 'available') {
                    throw ValidationException::withMessages([
                        'lot_id' => 'The selected new lot is not available.',
                    ]);
                }

                Lot::where('id', $oldLotId)->update([
                    'status' => 'available',
                ]);

                $newLot->update([
                    'status' => 'reserved',
                ]);
            }

            $reservation->update($data);

            return $reservation->fresh([
                'client',
                'lot.project',
                'lot.phase',
                'lot.block',
                'agent',
            ]);
        });
    }

    public function cancel(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation) {
            $reservation->update([
                'status' => 'cancelled',
            ]);

            $reservation->lot()->update([
                'status' => 'available',
            ]);

            return $reservation->fresh([
                'client',
                'lot.project',
                'lot.phase',
                'lot.block',
                'agent',
            ]);
        });
    }

    public function delete(Reservation $reservation): bool
    {
        return DB::transaction(function () use ($reservation) {
            if ($reservation->status === 'active') {
                $reservation->lot()->update([
                    'status' => 'available',
                ]);
            }

            return $reservation->delete();
        });
    }
}
