<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\PaymentSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentScheduleService
{
    public function generate(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::lockForUpdate()->findOrFail($data['sale_id']);

            if ($sale->status !== 'active') {
                throw ValidationException::withMessages([
                    'sale_id' => 'Payment schedule can only be generated for active sales.',
                ]);
            }

            $existingSchedules = PaymentSchedule::where('sale_id', $sale->id)->count();

            if ($existingSchedules > 0 && empty($data['overwrite'])) {
                throw ValidationException::withMessages([
                    'sale_id' => 'This sale already has a payment schedule.',
                ]);
            }

            if (! empty($data['overwrite'])) {
                PaymentSchedule::where('sale_id', $sale->id)->delete();
            }

            $balance = (float) $sale->balance;
            $months = (int) $data['months'];
            $startDate = Carbon::parse($data['start_date']);

            if ($balance <= 0) {
                throw ValidationException::withMessages([
                    'balance' => 'Sale balance must be greater than zero.',
                ]);
            }

            if ($months <= 0) {
                throw ValidationException::withMessages([
                    'months' => 'Number of months must be greater than zero.',
                ]);
            }

            $monthlyAmount = round($balance / $months, 2);
            $runningBalance = $balance;
            $schedules = [];

            for ($i = 1; $i <= $months; $i++) {
                $amountDue = $i === $months
                    ? round($runningBalance, 2)
                    : $monthlyAmount;

                $schedule = PaymentSchedule::create([
                    'sale_id' => $sale->id,
                    'installment_no' => $i,
                    'due_date' => $startDate->copy()->addMonths($i - 1),
                    'amount_due' => $amountDue,
                    'amount_paid' => 0,
                    'balance' => $amountDue,
                    'status' => 'pending',
                    'remarks' => null,
                ]);

                $runningBalance -= $amountDue;

                $schedules[] = $schedule;
            }

            return [
                'sale' => $sale->fresh([
                    'client',
                    'lot.project',
                    'agent',
                ]),
                'schedules' => $schedules,
            ];
        });
    }

    public function markOverdue(): int
    {
        return PaymentSchedule::query()
            ->whereIn('status', [
                'pending',
                'partial',
            ])
            ->whereDate('due_date', '<', now())
            ->update([
                'status' => 'overdue',
            ]);
    }
}
