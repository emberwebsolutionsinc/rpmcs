<?php

namespace App\Exports;

use App\Models\AgentCommissionPayment;
use Maatwebsite\Excel\Concerns\FromArray;

class CommissionPaymentReportExport implements FromArray
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function array(): array
    {
        $payments = $this->filteredQuery()
            ->with([
                'agent',
                'sale.client',
                'sale.lot.project',
                'createdBy',
            ])
            ->latest('payment_date')
            ->latest('id')
            ->get();

        $deletedPayments = $this->filteredQuery(true)
            ->with([
                'agent',
                'sale.client',
                'deletedBy',
            ])
            ->latest('deleted_at')
            ->get();

        $rows = [];

        $rows[] = ['RPMCS COMMISSION PAYMENT REPORT'];
        $rows[] = ['Generated', now()->format('F d, Y h:i A')];
        $rows[] = [];

        $rows[] = [
            'Active Payments',
            'Active Amount',
            'Deleted Payments',
            'Deleted Amount',
            'Net Paid',
        ];

        $rows[] = [
            $payments->count(),
            $payments->sum('amount'),
            $deletedPayments->count(),
            $deletedPayments->sum('amount'),
            $payments->sum('amount'),
        ];

        $rows[] = [];
        $rows[] = ['PAYMENT HISTORY'];
        $rows[] = [
            'Payment Date',
            'Agent',
            'Agent Code',
            'Sale No.',
            'Client',
            'Project',
            'Amount',
            'Method',
            'Reference No.',
            'Remarks',
            'Encoded By',
        ];

        foreach ($payments as $payment) {
            $rows[] = [
                optional($payment->payment_date)->format('Y-m-d'),
                $this->agentName($payment->agent),
                $payment->agent?->agent_code ?? '—',
                $payment->sale?->sale_no ?? '—',
                $this->clientName($payment),
                $payment->sale?->lot?->project?->project_name ?? '—',
                $payment->amount,
                $payment->payment_method,
                $payment->reference_no,
                $payment->remarks,
                $payment->createdBy?->name ?? $payment->createdBy?->email ?? '—',
            ];
        }

        $rows[] = [];
        $rows[] = ['DELETED / VOIDED PAYMENTS'];
        $rows[] = [
            'Deleted At',
            'Agent',
            'Sale No.',
            'Client',
            'Amount',
            'Reason',
            'Deleted By',
        ];

        foreach ($deletedPayments as $payment) {
            $rows[] = [
                optional($payment->deleted_at)->format('Y-m-d H:i'),
                $this->agentName($payment->agent),
                $payment->sale?->sale_no ?? '—',
                $this->clientName($payment),
                $payment->amount,
                $payment->delete_reason ?? '—',
                $payment->deletedBy?->name ?? $payment->deletedBy?->email ?? '—',
            ];
        }

        return $rows;
    }

    private function filteredQuery(bool $deleted = false)
    {
        $query = AgentCommissionPayment::query();

        if ($deleted) {
            $query->onlyTrashed();
        }

        if (!empty($this->filters['from_date'])) {
            $query->whereDate('payment_date', '>=', $this->filters['from_date']);
        }

        if (!empty($this->filters['to_date'])) {
            $query->whereDate('payment_date', '<=', $this->filters['to_date']);
        }

        if (!empty($this->filters['payment_method'])) {
            $query->where('payment_method', $this->filters['payment_method']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];

            $query->where(function ($query) use ($search) {
                $query->where('reference_no', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhereHas('agent', function ($query) use ($search) {
                        $query->where('agent_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale', function ($query) use ($search) {
                        $query->where('sale_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.client', function ($query) use ($search) {
                        $query->where('client_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    private function agentName($agent): string
    {
        if (! $agent) {
            return '—';
        }

        return trim(
            ($agent->first_name ?? '') . ' ' .
            ($agent->middle_name ?? '') . ' ' .
            ($agent->last_name ?? '')
        ) ?: '—';
    }

    private function clientName($payment): string
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
