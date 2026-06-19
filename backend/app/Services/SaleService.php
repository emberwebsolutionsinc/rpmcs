<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\Reservation;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {

            $lot = Lot::lockForUpdate()->findOrFail($data['lot_id']);

            if (! in_array($lot->status, ['available', 'reserved'])) {
                throw ValidationException::withMessages([
                    'lot_id' => 'This lot is not available for sale.',
                ]);
            }

            if (! empty($data['reservation_id'])) {
                $reservation = Reservation::lockForUpdate()
                    ->findOrFail($data['reservation_id']);

                if ($reservation->lot_id !== (int) $data['lot_id']) {
                    throw ValidationException::withMessages([
                        'reservation_id' => 'The reservation does not match the selected lot.',
                    ]);
                }

                if ($reservation->status !== 'active') {
                    throw ValidationException::withMessages([
                        'reservation_id' => 'Only active reservations may be converted to sale.',
                    ]);
                }

                $reservation->update([
                    'status' => 'converted_to_sale',
                ]);
            }

            $sale = Sale::create($data);

            $lot->update([
                'status' => 'sold',
            ]);

            return $sale->fresh([
                'reservation',
                'client',
                'lot.project',
                'lot.phase',
                'lot.block',
                'agent',
            ]);
        });
    }

    public function update(Sale $sale, array $data): Sale
    {
        return DB::transaction(function () use ($sale, $data) {

            $oldLotId = $sale->lot_id;
            $newLotId = (int) $data['lot_id'];

            if ($oldLotId !== $newLotId) {
                $newLot = Lot::lockForUpdate()->findOrFail($newLotId);

                if (! in_array($newLot->status, ['available', 'reserved'])) {
                    throw ValidationException::withMessages([
                        'lot_id' => 'The selected lot is not available for sale.',
                    ]);
                }

                Lot::where('id', $oldLotId)->update([
                    'status' => 'available',
                ]);

                $newLot->update([
                    'status' => 'sold',
                ]);
            }

            $sale->update($data);

            return $sale->fresh([
                'reservation',
                'client',
                'lot.project',
                'lot.phase',
                'lot.block',
                'agent',
            ]);
        });
    }

    public function cancel(Sale $sale): Sale
    {
        return DB::transaction(function () use ($sale) {

            $sale->update([
                'status' => 'cancelled',
            ]);

            $sale->lot()->update([
                'status' => 'available',
            ]);

            if ($sale->reservation_id) {
                $sale->reservation()->update([
                    'status' => 'cancelled',
                ]);
            }

            return $sale->fresh([
                'reservation',
                'client',
                'lot.project',
                'lot.phase',
                'lot.block',
                'agent',
            ]);
        });
    }

    public function delete(Sale $sale): bool
    {
        return DB::transaction(function () use ($sale) {

            if ($sale->status === 'active') {
                $sale->lot()->update([
                    'status' => 'available',
                ]);
            }

            return $sale->delete();
        });
    }
}
