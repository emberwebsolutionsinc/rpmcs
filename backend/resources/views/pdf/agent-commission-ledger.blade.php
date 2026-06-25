<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Agent Commission Ledger</title>

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
            AGENT COMMISSION LEDGER
        </div>

        <div class="generated">
            Generated: {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Agent</div>
                <div class="summary-value">{{ $summary['agent_name'] ?? '—' }}</div>
            </td>

            <td>
                <div class="summary-label">Agent Code</div>
                <div class="summary-value">{{ $summary['agent_code'] ?? '—' }}</div>
            </td>

            <td>
                <div class="summary-label">Sales</div>
                <div class="summary-value">{{ $summary['total_sales'] ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Earned</div>
                <div class="summary-value">
                    PHP {{ number_format($summary['total_commission_earned'] ?? 0, 2) }}
                </div>
            </td>

            <td>
                <div class="summary-label">Paid</div>
                <div class="summary-value">
                    PHP {{ number_format($summary['total_commission_paid'] ?? 0, 2) }}
                </div>
            </td>

            <td>
                <div class="summary-label">Balance</div>
                <div class="summary-value">
                    PHP {{ number_format($summary['total_commission_balance'] ?? 0, 2) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">
        Sales Commission Ledger
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Sale No.</th>
                <th>Date</th>
                <th>Client</th>
                <th>Property</th>
                <th class="right">Contract</th>
                <th class="center">Rate</th>
                <th class="right">Earned</th>
                <th class="right">Paid</th>
                <th class="right">Deleted</th>
                <th class="right">Balance</th>
            </tr>
        </thead>

        <tbody>
            @forelse($ledger as $row)
                <tr>
                    <td>{{ $row['sale_no'] ?? '—' }}</td>
                    <td>{{ optional($row['sale_date'])->format('Y-m-d') }}</td>
                    <td>
                        {{ trim(($row['client']->first_name ?? '') . ' ' . ($row['client']->last_name ?? '')) ?: '—' }}
                    </td>
                    <td>
                        {{ $row['project']->project_name ?? '—' }}
                        /
                        {{ $row['lot']->lot_no ?? '—' }}
                    </td>
                    <td class="right">{{ number_format($row['contract_price'] ?? 0, 2) }}</td>
                    <td class="center">{{ number_format($row['commission_rate'] ?? 0, 2) }}%</td>
                    <td class="right">{{ number_format($row['commission_earned'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($row['commission_paid'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($row['commission_deleted'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($row['commission_balance'] ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="center">
                        No ledger records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">
        Active Payment History
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Sale No.</th>
                <th>Client</th>
                <th class="right">Amount</th>
                <th>Method</th>
                <th>Reference</th>
            </tr>
        </thead>

        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ optional($payment->payment_date)->format('Y-m-d') }}</td>
                    <td>{{ $payment->sale?->sale_no ?? '—' }}</td>
                    <td>
                        {{ trim(($payment->sale?->client?->first_name ?? '') . ' ' . ($payment->sale?->client?->last_name ?? '')) ?: '—' }}
                    </td>
                    <td class="right">{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ strtoupper(str_replace('_', ' ', $payment->payment_method ?? '—')) }}</td>
                    <td>{{ $payment->reference_no ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">
                        No active payment records.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">
        Deleted / Voided Payment History
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Deleted At</th>
                <th>Sale No.</th>
                <th>Client</th>
                <th class="right">Amount</th>
                <th>Reason</th>
                <th>Deleted By</th>
            </tr>
        </thead>

        <tbody>
            @forelse($deletedPayments as $payment)
                <tr>
                    <td>{{ optional($payment->deleted_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ $payment->sale?->sale_no ?? '—' }}</td>
                    <td>
                        {{ trim(($payment->sale?->client?->first_name ?? '') . ' ' . ($payment->sale?->client?->last_name ?? '')) ?: '—' }}
                    </td>
                    <td class="right">{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->delete_reason ?? '—' }}</td>
                    <td>{{ $payment->deletedBy?->name ?? $payment->deletedBy?->email ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">
                        No deleted payment records.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        This agent commission ledger was generated by RPMCS.
    </div>
</body>
</html>
