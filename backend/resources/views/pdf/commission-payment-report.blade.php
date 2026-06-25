<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Commission Payment Report</title>

    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #0f172a;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #064e3b;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .company {
            font-size: 15px;
            font-weight: bold;
            color: #064e3b;
        }

        .title {
            margin-top: 4px;
            font-size: 13px;
            font-weight: bold;
        }

        .generated {
            margin-top: 3px;
            font-size: 8px;
            color: #475569;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .summary td {
            border: 1px solid #cbd5e1;
            padding: 5px;
            text-align: center;
        }

        .summary-label {
            font-size: 7px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }

        .summary-value {
            margin-top: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .section-title {
            margin: 10px 0 5px;
            font-size: 10px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        table.data th {
            background: #064e3b;
            color: #ffffff;
            border: 1px solid #064e3b;
            padding: 4px;
            font-size: 7px;
            text-transform: uppercase;
        }

        table.data td {
            border: 1px solid #cbd5e1;
            padding: 4px;
            font-size: 7px;
            vertical-align: top;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .footer {
            margin-top: 10px;
            border-top: 1px solid #cbd5e1;
            padding-top: 5px;
            text-align: center;
            font-size: 7px;
            color: #64748b;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company">
            BANTOG REALTY PROPERTY MANAGEMENT
        </div>

        <div class="title">
            COMMISSION PAYMENT REPORT
        </div>

        <div class="generated">
            Generated: {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Active Payments</div>
                <div class="summary-value">{{ $summary['total_active_payments'] ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Active Amount</div>
                <div class="summary-value">
                    PHP {{ number_format($summary['total_active_amount'] ?? 0, 2) }}
                </div>
            </td>

            <td>
                <div class="summary-label">Deleted Payments</div>
                <div class="summary-value">{{ $summary['total_deleted_payments'] ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Deleted Amount</div>
                <div class="summary-value">
                    PHP {{ number_format($summary['total_deleted_amount'] ?? 0, 2) }}
                </div>
            </td>

            <td>
                <div class="summary-label">Net Paid</div>
                <div class="summary-value">
                    PHP {{ number_format($summary['net_payment_amount'] ?? 0, 2) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">
        Payment History
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Agent</th>
                <th>Sale No.</th>
                <th>Client</th>
                <th class="right">Amount</th>
                <th>Method</th>
                <th>Reference</th>
                <th>Encoded By</th>
            </tr>
        </thead>

        <tbody>
            @forelse($payments as $payment)
                @php
                    $agentName = $payment->agent
                        ? trim(($payment->agent->first_name ?? '') . ' ' . ($payment->agent->last_name ?? ''))
                        : '—';

                    $clientName = $payment->sale?->client
                        ? trim(($payment->sale->client->first_name ?? '') . ' ' . ($payment->sale->client->last_name ?? ''))
                        : '—';

                    $encodedBy = $payment->createdBy
                        ? ($payment->createdBy->name ?? $payment->createdBy->email ?? '—')
                        : '—';
                @endphp

                <tr>
                    <td>{{ optional($payment->payment_date)->format('Y-m-d') }}</td>
                    <td>{{ $agentName ?: '—' }}</td>
                    <td>{{ $payment->sale?->sale_no ?? '—' }}</td>
                    <td>{{ $clientName ?: '—' }}</td>
                    <td class="right">{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ strtoupper(str_replace('_', ' ', $payment->payment_method ?? '—')) }}</td>
                    <td>{{ $payment->reference_no ?? '—' }}</td>
                    <td>{{ $encodedBy }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center">
                        No commission payments found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">
        Deleted / Voided Commission Payments
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Deleted At</th>
                <th>Agent</th>
                <th>Sale No.</th>
                <th>Client</th>
                <th class="right">Amount</th>
                <th>Reason</th>
                <th>Deleted By</th>
            </tr>
        </thead>

        <tbody>
            @forelse($deletedPayments as $payment)
                @php
                    $agentName = $payment->agent
                        ? trim(($payment->agent->first_name ?? '') . ' ' . ($payment->agent->last_name ?? ''))
                        : '—';

                    $clientName = $payment->sale?->client
                        ? trim(($payment->sale->client->first_name ?? '') . ' ' . ($payment->sale->client->last_name ?? ''))
                        : '—';

                    $deletedBy = $payment->deletedBy
                        ? ($payment->deletedBy->name ?? $payment->deletedBy->email ?? '—')
                        : '—';
                @endphp

                <tr>
                    <td>{{ optional($payment->deleted_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ $agentName ?: '—' }}</td>
                    <td>{{ $payment->sale?->sale_no ?? '—' }}</td>
                    <td>{{ $clientName ?: '—' }}</td>
                    <td class="right">{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->delete_reason ?? '—' }}</td>
                    <td>{{ $deletedBy }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">
                        No deleted commission payments.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        This report was generated by RPMCS. Deleted payments are shown for audit trail purposes.
    </div>
</body>
</html>
