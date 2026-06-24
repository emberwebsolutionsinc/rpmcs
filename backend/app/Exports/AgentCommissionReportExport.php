<?php

namespace App\Exports;

use App\Models\AgentCommissionPayment;
use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromArray;

class AgentCommissionReportExport implements FromArray
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function array(): array
    {
        $sales = $this->filteredSales()
            ->with([
                'agent',
                'client',
                'lot.project',
            ])
            ->latest('sale_date')
            ->latest('id')
            ->get();

        $rows = [];

        $agentRows = $sales
            ->groupBy('agent_id')
            ->map(function ($agentSales) {
                $agent = $agentSales->first()?->agent;

                $rate = (float) ($agent?->default_commission_rate ?? 0);
                $contractPrice = $agentSales->sum('contract_price');
                $commission = $contractPrice * ($rate / 100);

                $commissionPaid = AgentCommissionPayment::query()
                    ->where('agent_id', $agent?->id)
                    ->sum('amount');

                $commissionDeleted = AgentCommissionPayment::onlyTrashed()
                    ->where('agent_id', $agent?->id)
                    ->sum('amount');

                return [
                    'agent_code' => $agent?->agent_code ?? '—',
                    'agent_name' => trim(
                        ($agent?->first_name ?? '') . ' ' .
                        ($agent?->middle_name ?? '') . ' ' .
                        ($agent?->last_name ?? '')
                    ) ?: 'Unassigned Agent',
                    'agent_type' => $agent?->agent_type ?? '—',
                    'sales_count' => $agentSales->count(),
                    'total_contract_price' => $contractPrice,
                    'total_downpayment' => $agentSales->sum('downpayment'),
                    'total_balance' => $agentSales->sum('balance'),
                    'commission_rate' => $rate,
                    'commission_earned' => $commission,
                    'commission_paid' => $commissionPaid,
                    'commission_deleted' => $commissionDeleted,
                    'commission_balance' => $commission - $commissionPaid,
                ];
            })
            ->values();

        $summary = [
            'total_agents' => $agentRows->count(),
            'total_sales' => $agentRows->sum('sales_count'),
            'total_contract_price' => $agentRows->sum('total_contract_price'),
            'total_downpayment' => $agentRows->sum('total_downpayment'),
            'total_balance' => $agentRows->sum('total_balance'),
            'gross_commission' => $agentRows->sum('commission_earned'),
            'paid_commission' => $agentRows->sum('commission_paid'),
            'deleted_commission' => $agentRows->sum('commission_deleted'),
            'unpaid_commission' => $agentRows->sum('commission_balance'),
        ];

        $rows[] = ['RPMCS AGENT COMMISSION REPORT'];
        $rows[] = ['Generated', now()->format('F d, Y h:i A')];
        $rows[] = [];

        $rows[] = [
            'Total Agents',
            'Total Sales',
            'Total Contract Price',
            'Total Downpayment',
            'Total Balance',
            'Gross Commission',
            'Paid Commission',
            'Deleted Commission',
            'Unpaid Commission',
        ];

        $rows[] = [
            $summary['total_agents'],
            $summary['total_sales'],
            $summary['total_contract_price'],
            $summary['total_downpayment'],
            $summary['total_balance'],
            $summary['gross_commission'],
            $summary['paid_commission'],
            $summary['deleted_commission'],
            $summary['unpaid_commission'],
        ];

        $rows[] = [];
        $rows[] = ['AGENT SUMMARY'];
        $rows[] = [
            'Agent Code',
            'Agent Name',
            'Agent Type',
            'Sales Count',
            'Contract Price',
            'Downpayment',
            'Balance',
            'Rate',
            'Commission Earned',
            'Commission Paid',
            'Deleted Commission',
            'Commission Balance',
        ];

        foreach ($agentRows as $agent) {
            $rows[] = [
                $agent['agent_code'],
                $agent['agent_name'],
                $agent['agent_type'],
                $agent['sales_count'],
                $agent['total_contract_price'],
                $agent['total_downpayment'],
                $agent['total_balance'],
                $agent['commission_rate'] . '%',
                $agent['commission_earned'],
                $agent['commission_paid'],
                $agent['commission_deleted'],
                $agent['commission_balance'],
            ];
        }

        $rows[] = [];
        $rows[] = ['COMMISSION DETAILS PER SALE'];
        $rows[] = [
            'Sale No.',
            'Client',
            'Agent',
            'Project',
            'Lot',
            'Contract Price',
            'Downpayment',
            'Balance',
            'Rate',
            'Commission Earned',
            'Commission Paid',
            'Deleted Commission',
            'Commission Balance',
        ];

        foreach ($sales as $sale) {
            $clientName = $sale->client
                ? trim(($sale->client->first_name ?? '') . ' ' . ($sale->client->last_name ?? ''))
                : '—';

            $agentName = $sale->agent
                ? trim(($sale->agent->first_name ?? '') . ' ' . ($sale->agent->last_name ?? ''))
                : '—';

            $rate = (float) ($sale->agent?->default_commission_rate ?? 0);
            $commission = (float) $sale->contract_price * ($rate / 100);

            $commissionPaid = AgentCommissionPayment::query()
                ->where('sale_id', $sale->id)
                ->sum('amount');

            $commissionDeleted = AgentCommissionPayment::onlyTrashed()
                ->where('sale_id', $sale->id)
                ->sum('amount');

            $rows[] = [
                $sale->sale_no,
                $clientName,
                $agentName,
                $sale->lot?->project?->project_name ?? '—',
                $sale->lot?->lot_no ?? '—',
                $sale->contract_price,
                $sale->downpayment,
                $sale->balance,
                $rate . '%',
                $commission,
                $commissionPaid,
                $commissionDeleted,
                $commission - $commissionPaid,
            ];
        }

        $deletedPayments = AgentCommissionPayment::onlyTrashed()
            ->with([
                'agent',
                'sale.client',
                'deletedBy',
            ])
            ->latest('deleted_at')
            ->get();

        $rows[] = [];
        $rows[] = ['DELETED / VOIDED COMMISSION PAYMENTS'];
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
            $agentName = $payment->agent
                ? trim(($payment->agent->first_name ?? '') . ' ' . ($payment->agent->last_name ?? ''))
                : '—';

            $clientName = $payment->sale?->client
                ? trim(($payment->sale->client->first_name ?? '') . ' ' . ($payment->sale->client->last_name ?? ''))
                : '—';

            $deletedBy = $payment->deletedBy
                ? ($payment->deletedBy->name ?? $payment->deletedBy->email ?? '—')
                : '—';

            $rows[] = [
                optional($payment->deleted_at)->format('Y-m-d H:i'),
                $agentName,
                $payment->sale?->sale_no ?? '—',
                $clientName,
                $payment->amount,
                $payment->delete_reason ?? '—',
                $deletedBy,
            ];
        }

        return $rows;
    }

    private function filteredSales()
    {
        $query = Sale::query()
            ->whereNotNull('agent_id')
            ->where('status', '!=', 'cancelled');

        if (!empty($this->filters['from_date'])) {
            $query->whereDate('sale_date', '>=', $this->filters['from_date']);
        }

        if (!empty($this->filters['to_date'])) {
            $query->whereDate('sale_date', '<=', $this->filters['to_date']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];

            $query->where(function ($query) use ($search) {
                $query->where('sale_no', 'like', "%{$search}%")
                    ->orWhereHas('agent', function ($query) use ($search) {
                        $query->where('agent_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('client', function ($query) use ($search) {
                        $query->where('client_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('lot', function ($query) use ($search) {
                        $query->where('lot_no', 'like', "%{$search}%")
                            ->orWhere('lot_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('lot.project', function ($query) use ($search) {
                        $query->where('project_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }
}
