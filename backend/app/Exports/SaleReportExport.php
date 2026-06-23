<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SaleReportExport implements FromCollection, WithHeadings
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Sale::query()
            ->with([
                'client',
                'property',
                'agent',
            ]);

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('sale_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('sale_date', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['agent_id'])) {
            $query->where('agent_id', $this->filters['agent_id']);
        }

        return $query->latest('sale_date')->get()->map(function ($sale) {
            return [
                'Sale ID' => $sale->id,
                'Sale Date' => $sale->sale_date,
                'Client' => $sale->client->name ?? '',
                'Property' => $sale->property->name ?? '',
                'Agent' => $sale->agent->name ?? '',
                'Total Contract Price' => $sale->total_contract_price ?? 0,
                'Reservation Fee' => $sale->reservation_fee ?? 0,
                'Downpayment' => $sale->downpayment ?? 0,
                'Commission Amount' => $sale->commission_amount ?? 0,
                'Status' => $sale->status ?? '',
                'Remarks' => $sale->remarks ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Sale ID',
            'Sale Date',
            'Client',
            'Property',
            'Agent',
            'Total Contract Price',
            'Reservation Fee',
            'Downpayment',
            'Commission Amount',
            'Status',
            'Remarks',
        ];
    }
}
