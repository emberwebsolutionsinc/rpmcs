<?php

namespace App\Services;

use App\Models\Lot;
use Illuminate\Support\Facades\DB;

class LotService
{
    public function create(array $data): Lot
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['selling_price'])) {
                $data['selling_price'] = $data['lot_area'] * $data['price_per_sqm'];
            }

            return Lot::create($data);
        });
    }

    public function update(Lot $lot, array $data): Lot
    {
        return DB::transaction(function () use ($lot, $data) {
            if (empty($data['selling_price'])) {
                $data['selling_price'] = $data['lot_area'] * $data['price_per_sqm'];
            }

            $lot->update($data);

            return $lot->fresh();
        });
    }

    public function delete(Lot $lot): bool
    {
        return DB::transaction(fn () => $lot->delete());
    }
}
