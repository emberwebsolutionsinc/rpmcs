<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentCommissionPayment;
use Barryvdh\DomPDF\Facade\Pdf;

class CommissionPaymentVoucherController extends Controller
{
    public function print(AgentCommissionPayment $payment)
    {
        $payment->load([
            'agent',
            'sale.client',
            'sale.lot.project',
            'createdBy',
        ]);

        $pdf = Pdf::loadView('pdf.commission-payment-voucher', [
            'payment' => $payment,
        ])->setPaper('a4', 'portrait');

        return $pdf->download(
            'commission-payment-voucher-' .
            $payment->id .
            '-' .
            now()->format('Y-m-d-His') .
            '.pdf'
        );
    }
}
