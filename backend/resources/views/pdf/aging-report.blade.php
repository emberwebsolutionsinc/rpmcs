<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Aging Report</title>

    <style>
        @page {
            margin: 18px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #0f172a;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
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
            letter-spacing: .5px;
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
            padding: 6px;
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
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
        }

        .section-title {
            margin: 10px 0 5px;
            font-size: 10px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
        }

        .bucket-table,
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bucket-table th,
        .data-table th {
            background: #064e3b;
            color: #ffffff;
            border: 1px solid #064e3b;
            padding: 4px;
            font-size: 7px;
            text-transform: uppercase;
        }

        .bucket-table td,
        .data-table td {
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
            font-size: 7px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company">
            BANTOG REALTY PROPERTY MANAGEMENT
        </div>

        <div class="title">
            AGING REPORT
        </div>

        <div class="generated">
            Generated: {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Accounts</div>
                <div class="summary-value">{{ $summary['total_accounts'] }}</div>
            </td>

            <td>
                <div class="summary-label">Installments</div>
                <div class="summary-value">{{ $summary['total_installments'] }}</div>
            </td>

            <td>
                <div class="summary-label">Total Balance</div>
                <div class="summary-value">PHP {{ number_format($summary['total_balance'], 2) }}</div>
            </td>

            <td>
                <div class="summary-label">Current</div>
                <div class="summary-value">PHP {{ number_format($summary['current_amount'], 2) }}</div>
            </td>

            <td>
                <div class="summary-label">Overdue</div>
                <div class="summary-value">PHP {{ number_format($summary['overdue_amount'], 2) }}</div>
            </td>

            <td>
                <div class="summary-label">Critical</div>
                <div class="summary-value">PHP {{ number_format($summary['critical_amount'], 2) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">
        Aging Bucket Summary
    </div>

    <table class="bucket-table">
        <thead>
            <tr>
                <th>Bucket</th>
                <th class="center">Count</th>
                <th class="right">Amount</th>
            </tr>
        </thead>

        <tbody>
            @foreach($buckets as $bucket)
                <tr>
                    <td>{{ $bucket['label'] }}</td>
                    <td class="center">{{ $bucket['count'] }}</td>
                    <td class="right">{{ number_format($bucket['amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">
        Detailed Aging Records
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Client</th>
                <th>Sale No.</th>
                <th>Lot</th>
                <th>Inst.</th>
                <th>Due Date</th>
                <th>Days</th>
                <th class="right">Due</th>
                <th class="right">Paid</th>
                <th class="right">Balance</th>
                <th>Bucket</th>
                <th>Risk</th>
            </tr>
        </thead>

        <tbody>
            @foreach($records as $record)
                @php
                    $client = $record->sale?->client;

                    $clientName = $client
                        ? trim(
                            ($client->first_name ?? '') . ' ' .
                            ($client->last_name ?? '')
                        )
                        : '—';
                @endphp

                <tr>
                    <td>{{ $clientName ?: '—' }}</td>
                    <td>{{ $record->sale?->sale_no ?? '—' }}</td>
                    <td>{{ $record->sale?->lot?->lot_no ?? '—' }}</td>
                    <td class="center">{{ $record->installment_no }}</td>
                    <td>{{ optional($record->due_date)->format('Y-m-d') }}</td>
                    <td class="center">{{ $record->days_late }}</td>
                    <td class="right">{{ number_format($record->amount_due, 2) }}</td>
                    <td class="right">{{ number_format($record->amount_paid, 2) }}</td>
                    <td class="right">{{ number_format($record->balance, 2) }}</td>
                    <td>{{ $record->aging_bucket }}</td>
                    <td>{{ strtoupper($record->risk_level) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        This report was generated by RPMCS. Please verify all figures before official submission.
    </div>
</body>
</html>
