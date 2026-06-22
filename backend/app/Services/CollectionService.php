<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\PaymentSchedule;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CollectionService
{
    public function create(array $data): Collection
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::lockForUpdate()->findOrFail($data['sale_id']);

            if ($sale->status !== 'active') {
                throw ValidationException::withMessages([
                    'sale_id' => 'Payments can only be posted to active sales.',
                ]);
            }

            $amountPaid = (float) $data['amount_paid'];

            if ($amountPaid > (float) $sale->balance) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Payment amount cannot exceed the sale balance.',
                ]);
            }

            $schedule = null;

            if (! empty($data['payment_schedule_id'])) {
                $schedule = PaymentSchedule::lockForUpdate()
                    ->where('sale_id', $sale->id)
                    ->findOrFail($data['payment_schedule_id']);

                if (in_array($schedule->status, ['paid', 'cancelled'])) {
                    throw ValidationException::withMessages([
                        'payment_schedule_id' => 'This installment is already paid or cancelled.',
                    ]);
                }

                if ($amountPaid > (float) $schedule->balance) {
                    throw ValidationException::withMessages([
                        'amount_paid' => 'Payment amount cannot exceed the installment balance.',
                    ]);
                }
            }

            $collection = Collection::create([
                'collection_no' => $this->generateCollectionNo(),
                'sale_id' => $sale->id,
                'payment_schedule_id' => $data['payment_schedule_id'] ?? null,
                'official_receipt_no' => !empty($data['official_receipt_no'])
                ? $data['official_receipt_no']
                : $this->generateOfficialReceiptNo(),
                'payment_date' => $data['payment_date'],
                'amount_paid' => $amountPaid,
                'payment_method' => $data['payment_method'],
                'reference_no' => $data['reference_no'] ?? null,
                'status' => 'posted',
                'remarks' => $data['remarks'] ?? null,
            ]);

            if ($schedule) {
                $newAmountPaid = (float) $schedule->amount_paid + $amountPaid;
                $newBalance = (float) $schedule->balance - $amountPaid;

                $schedule->update([
                    'amount_paid' => $newAmountPaid,
                    'balance' => $newBalance,
                    'status' => $newBalance <= 0 ? 'paid' : 'partial',
                ]);
            }

            $newSaleBalance = (float) $sale->balance - $amountPaid;

            $sale->update([
                'balance' => $newSaleBalance,
                'status' => $newSaleBalance <= 0 ? 'fully_paid' : 'active',
            ]);

            return $collection->fresh([
                'sale.client',
                'sale.lot.project',
                'paymentSchedule',
            ]);
        });
    }

        public function void(
            Collection $collection,
            array $data,
            ?int $userId = null
        ): Collection {
            return DB::transaction(function () use ($collection, $data, $userId) {
                if ($collection->status === 'voided') {
                    throw ValidationException::withMessages([
                        'collection' => 'This collection is already voided.',
                    ]);
                }

                $sale = Sale::lockForUpdate()->findOrFail($collection->sale_id);

                $schedule = null;

                if ($collection->payment_schedule_id) {
                    $schedule = PaymentSchedule::lockForUpdate()
                        ->findOrFail($collection->payment_schedule_id);
                }

                $amount = (float) $collection->amount_paid;

                if ($schedule) {
                    $restoredPaid = max(
                        0,
                        (float) $schedule->amount_paid - $amount
                    );

                    $restoredBalance = (float) $schedule->balance + $amount;

                    $scheduleStatus = 'partial';

                    if ($restoredPaid <= 0) {
                        $scheduleStatus = 'pending';
                    }

                    if ($restoredBalance <= 0) {
                        $scheduleStatus = 'paid';
                    }

                    $schedule->update([
                        'amount_paid' => $restoredPaid,
                        'balance' => $restoredBalance,
                        'status' => $scheduleStatus,
                    ]);
                }

                $sale->update([
                    'balance' => (float) $sale->balance + $amount,
                    'status' => 'active',
                ]);

                $collection->update([
                    'status' => 'voided',
                    'voided_by' => $userId,
                    'voided_at' => now(),
                    'void_reason' => $data['void_reason'],
                ]);

                return $collection->fresh([
                    'sale.client',
                    'sale.lot.project',
                    'paymentSchedule',
                    'voidedBy',
                ]);
            });
}

    protected function generateCollectionNo(): string
    {
        $year = now()->format('Y');

        $latest = Collection::query()
            ->whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $nextNumber = $latest
            ? ((int) substr($latest->collection_no, -6)) + 1
            : 1;

        return 'COL-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    protected function generateOfficialReceiptNo(): string
    {
        $year = now()->format('Y');

        $latest = Collection::query()
            ->whereNotNull('official_receipt_no')
            ->latest('id')
            ->first();

        $nextNumber = $latest
            ? ((int) substr($latest->official_receipt_no, -6)) + 1
            : 1;

        return 'OR-' . $year . '-' . str_pad(
            $nextNumber,
            6,
            '0',
            STR_PAD_LEFT
        );
    }
}
