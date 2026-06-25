<?php

namespace App\Exports;

use App\Models\Agent;
use App\Models\AgentCommissionPayment;
use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromArray;

class AgentCommissionLedgerExport implements FromArray
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function array(): array
    {
        $agent = Agent::query()
            ->findOrFail($this->filters['agent_id']);

        $sales = $this->filteredSales($agent)
            ->with([
                'client',
                'lot.project',
            ])
            ->latest('sale_date')
            ->latest('id')
            ->get();

        $payments = $this->filteredPayments($agent)
            ->with([
                'sale.client',
                'createdBy',
            ])
            ->latest('payment_date')
            ->latest('id')
            ->get();

        $deletedPayments = $this->filteredDeletedPayments($agent)
            ->with([
                'sale.client',
                'deletedBy',
            ])
            ->latest('deleted_at')
            ->get();

        $ledgerRows = $sales->map(function ($sale) use ($agent) {
            $rate = (float) ($agent->default_commission_rate ?? 0);
            $earned = (float) $sale->contract_price * ($rate / 100);

            $paid = AgentCommissionPayment::query()
                ->where('sale_id', $sale->id)
                ->sum('amount');

            $deleted = AgentCommissionPayment::onlyTrashed()
                ->where('sale_id', $sale->id)
                ->sum('amount');

            return [
                'sale_no' => $sale->sale_no,
                'sale_date' => $sale->sale_date,
                'client' => $this->clientName($sale),
                'project' => $sale->lot?->project?->project_name ?? '—',
                'lot' => $sale->lot?->lot_no ?? '—',
                'contract_price' => (float) $sale->contract_price,
                'commission_rate' => $rate,
                'commission_earned' => $earned,
                'commission_paid' => (float) $paid,
                'commission_deleted' => (float) $deleted,
                'commission_balance' => max($earned - $paid, 0),
            ];
        });

        $rows = [];

        $rows[] = ['RPMCS AGENT COMMISSION LEDGER'];
        $rows[] = ['Generated', now()->format('F d, Y h:i A')];
        $rows[] = ['Agent', $this->agentName($agent)];
        $rows[] = ['Agent Code', $agent->agent_code];
        $rows[] = ['Commission Rate', ((float) $agent->default_commission_rate) . '%'];
        $rows[] = [];

        $rows[] = [
            'Total Sales',
            'Total Contract Price',
            'Total Commission Earned',
            'Total Commission Paid',
            'Total Deleted / Voided',
            'Total Commission Balance',
        ];

        $rows[] = [
            $ledgerRows->count(),
            $ledgerRows->sum('contract_price'),
            $ledgerRows->sum('commission_earned'),
            $ledgerRows->sum('commission_paid'),
            $ledgerRows->sum('commission_deleted'),
            $ledgerRows->sum('commission_balance'),
        ];

        $rows[] = [];
        $rows[] = ['SALES COMMISSION LEDGER'];
        $rows[] = [
            'Sale No.',
            'Sale Date',
            'Client',
            'Project',
            'Lot',
            'Contract Price',
            'Rate',
            'Earned',
            'Paid',
            'Deleted / Voided',
            'Balance',
        ];

        foreach ($ledgerRows as $row) {
            $rows[] = [
                $row['sale_no'],
                optional($row['sale_date'])->format('Y-m-d'),
                $row['client'],
                $row['project'],
                $row['lot'],
                $row['contract_price'],
                $row['commission_rate'] . '%',
                $row['commission_earned'],
                $row['commission_paid'],
                $row['commission_deleted'],
                $row['commission_balance'],
            ];
        }

        $rows[] = [];
        $rows[] = ['ACTIVE PAYMENT HISTORY'];
        $rows[] = [
            'Payment Date',
            'Sale No.',
            'Client',
            'Amount',
            'Method',
            'Reference No.',
            'Remarks',
            'Encoded By',
        ];

        foreach ($payments as $payment) {
            $rows[] = [
                optional($payment->payment_date)->format('Y-m-d'),
                $payment->sale?->sale_no ?? '—',
                $this->paymentClientName($payment),
                $payment->amount,
                $payment->payment_method,
                $payment->reference_no,
                $payment->remarks,
                $payment->createdBy?->name ?? $payment->createdBy?->email ?? '—',
            ];
        }

        $rows[] = [];
        $rows[] = ['DELETED / VOIDED PAYMENT HISTORY'];
        $rows[] = [
            'Deleted At',
            'Sale No.',
            'Client',
            'Amount',
            'Reason',
            'Deleted By',
        ];

        foreach ($deletedPayments as $payment) {
            $rows[] = [
                optional($payment->deleted_at)->format('Y-m-d H:i'),
                $payment->sale?->sale_no ?? '—',
                $this->paymentClientName($payment),
                $payment->amount,
                $payment->delete_reason ?? '—',
                $payment->deletedBy?->name ?? $payment->deletedBy?->email ?? '—',
            ];
        }

        return $rows;
    }

    private function filteredSales(Agent $agent)
    {
        return Sale::query()
            ->where('agent_id', $agent->id)
            ->where('status', '!=', 'cancelled')
            ->when(
                !empty($this->filters['from_date']),
                fn ($query) => $query->whereDate('sale_date', '>=', $this->filters['from_date'])
            )
            ->when(
                !empty($this->filters['to_date']),
                fn ($query) => $query->whereDate('sale_date', '<=', $this->filters['to_date'])
            );
    }

    private function filteredPayments(Agent $agent)
    {
        return AgentCommissionPayment::query()
            ->where('agent_id', $agent->id)
            ->when(
                !empty($this->filters['from_date']),
                fn ($query) => $query->whereDate('payment_date', '>=', $this->filters['from_date'])
            )
            ->when(
                !empty($this->filters['to_date']),
                fn ($query) => $query->whereDate('payment_date', '<=', $this->filters['to_date'])
            );
    }

    private function filteredDeletedPayments(Agent $agent)
    {
        return AgentCommissionPayment::onlyTrashed()
            ->where('agent_id', $agent->id);
    }

    private function agentName(Agent $agent): string
    {
        return trim(
            ($agent->first_name ?? '') . ' ' .
            ($agent->middle_name ?? '') . ' ' .
            ($agent->last_name ?? '')
        ) ?: '—';
    }

    private function clientName($sale): string
    {
        $client = $sale->client;

        if (! $client) {
            return '—';
        }

        return trim(
            ($client->first_name ?? '') . ' ' .
            ($client->middle_name ?? '') . ' ' .
            ($client->last_name ?? '')
        ) ?: '—';
    }

    private function paymentClientName($payment): string
    {
        $client = $payment->sale?->client;

        if (! $client) {
            return '—';
        }

        return trim(
            ($client->first_name ?? '') . ' ' .
            ($client->middle_name ?? '') . ' ' .
            ($client->last_name ?? '')
        ) ?: '—';
    }
}
