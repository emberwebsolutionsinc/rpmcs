<?php

namespace App\Exports;

use App\Models\Collection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CollectionReportExport implements FromView
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function view(): View
    {
        $query = Collection::query()
            ->with([
                'sale.client',
                'sale.lot.project',
                'paymentSchedule',
            ]);

        if (!empty($this->filters['from_date'])) {
            $query->whereDate(
                'payment_date',
                '>=',
                $this->filters['from_date']
            );
        }

        if (!empty($this->filters['to_date'])) {
            $query->whereDate(
                'payment_date',
                '<=',
                $this->filters['to_date']
            );
        }

        if (!empty($this->filters['status'])) {
            $query->where(
                'status',
                $this->filters['status']
            );
        }

        if (!empty($this->filters['payment_method'])) {
            $query->where(
                'payment_method',
                $this->filters['payment_method']
            );
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];

            $query->where(function ($query) use ($search) {
                $query->where('collection_no', 'like', "%{$search}%")
                    ->orWhere('official_receipt_no', 'like', "%{$search}%")
                    ->orWhereHas('sale', function ($query) use ($search) {
                        $query->where('sale_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.client', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('client_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.lot.project', function ($query) use ($search) {
                        $query->where('project_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.lot', function ($query) use ($search) {
                        $query->where('lot_no', 'like', "%{$search}%")
                            ->orWhere('lot_code', 'like', "%{$search}%");
                    });
            });
        }

        $collections = $query
            ->latest('payment_date')
            ->latest('id')
            ->get();

        $postedCount = $collections
            ->where('status', 'posted')
            ->count();

        $voidedCount = $collections
            ->where('status', 'voided')
            ->count();

        $grossCollections = $collections
            ->where('status', 'posted')
            ->sum('amount_paid');

        $voidedAmount = $collections
            ->where('status', 'voided')
            ->sum('amount_paid');

        return view('exports.collection-report', [
            'collections' => $collections,
            'summary' => [
                'posted_count' => $postedCount,
                'voided_count' => $voidedCount,
                'gross_collections' => $grossCollections,
                'voided_amount' => $voidedAmount,
                'net_collections' => $grossCollections,
            ],
            'filters' => $this->filters,
        ]);
    }
}
