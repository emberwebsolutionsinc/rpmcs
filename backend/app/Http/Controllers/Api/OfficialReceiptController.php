<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Barryvdh\DomPDF\Facade\Pdf;

class OfficialReceiptController extends Controller
{
    public function print(Collection $collection)
    {
        $collection->load([
            'sale.client',
            'sale.lot.project',
            'paymentSchedule',
        ]);

        $pdf = Pdf::loadView('pdf.official-receipt', [
            'collection' => $collection,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream(
            'official-receipt-' . $collection->official_receipt_no . '.pdf'
        );
    }
}
