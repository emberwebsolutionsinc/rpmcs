<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Commission Payment Voucher</title>

    <style>
        @page {
            margin: 36px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0f172a;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #064e3b;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .company {
            font-size: 18px;
            font-weight: bold;
            color: #064e3b;
        }

        .title {
            margin-top: 6px;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .meta {
            margin-top: 6px;
            font-size: 10px;
            color: #64748b;
        }

        .section-title {
            margin-top: 18px;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            vertical-align: top;
        }

        .label {
            width: 28%;
            background: #f8fafc;
            font-weight: bold;
            color: #334155;
        }

        .amount-box {
            margin-top: 18px;
            border: 2px solid #064e3b;
            padding: 14px;
            text-align: center;
        }

        .amount-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
        }

        .amount {
            margin-top: 6px;
            font-size: 24px;
            font-weight: bold;
            color: #064e3b;
        }

        .signature-section {
            margin-top: 50px;
            width: 100%;
        }

        .signature-table td {
            border: none;
            padding-top: 40px;
            text-align: center;
            width: 50%;
        }

        .line {
            border-top: 1px solid #0f172a;
            padding-top: 6px;
            font-weight: bold;
        }

        .small {
            font-size: 10px;
            color: #64748b;
        }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            font-size: 10px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>

<body>
    @php
        $agentName = $payment->agent
            ? trim(
                ($payment->agent->first_name ?? '') . ' ' .
                ($payment->agent->middle_name ?? '') . ' ' .
                ($payment->agent->last_name ?? '')
            )
            : '—';

        $clientName = $payment->sale?->client
            ? trim(
                ($payment->sale->client->first_name ?? '') . ' ' .
                ($payment->sale->client->middle_name ?? '') . ' ' .
                ($payment->sale->client->last_name ?? '')
            )
            : '—';

        $preparedBy = $payment->createdBy
            ? ($payment->createdBy->name ?? $payment->createdBy->email ?? '—')
            : '—';
    @endphp

    <div class="header">
        <div class="company">
            BANTOG REALTY PROPERTY MANAGEMENT
        </div>

        <div class="title">
            COMMISSION PAYMENT VOUCHER
        </div>

        <div class="meta">
            Voucher generated on {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>

    <div class="section-title">
        Payment Details
    </div>

    <table>
        <tr>
            <td class="label">Voucher No.</td>
            <td>CPV-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td>
        </tr>

        <tr>
            <td class="label">Payment Date</td>
            <td>{{ optional($payment->payment_date)->format('F d, Y') }}</td>
        </tr>

        <tr>
            <td class="label">Payment Method</td>
            <td>{{ strtoupper(str_replace('_', ' ', $payment->payment_method ?? '—')) }}</td>
        </tr>

        <tr>
            <td class="label">Reference No.</td>
            <td>{{ $payment->reference_no ?? '—' }}</td>
        </tr>
    </table>

    <div class="section-title">
        Agent / Sale Information
    </div>

    <table>
        <tr>
            <td class="label">Agent</td>
            <td>{{ $agentName ?: '—' }}</td>
        </tr>

        <tr>
            <td class="label">Agent Code</td>
            <td>{{ $payment->agent?->agent_code ?? '—' }}</td>
        </tr>

        <tr>
            <td class="label">Sale No.</td>
            <td>{{ $payment->sale?->sale_no ?? '—' }}</td>
        </tr>

        <tr>
            <td class="label">Client</td>
            <td>{{ $clientName ?: '—' }}</td>
        </tr>

        <tr>
            <td class="label">Project</td>
            <td>{{ $payment->sale?->lot?->project?->project_name ?? '—' }}</td>
        </tr>

        <tr>
            <td class="label">Lot</td>
            <td>{{ $payment->sale?->lot?->lot_no ?? '—' }}</td>
        </tr>
    </table>

    <div class="amount-box">
        <div class="amount-label">
            Commission Amount Paid
        </div>

        <div class="amount">
            PHP {{ number_format($payment->amount, 2) }}
        </div>
    </div>

    <div class="section-title">
        Remarks
    </div>

    <table>
        <tr>
            <td>{{ $payment->remarks ?? '—' }}</td>
        </tr>
    </table>

    <table class="signature-table signature-section">
        <tr>
            <td>
                <div class="line">
                    {{ $preparedBy }}
                </div>
                <div class="small">
                    Prepared By
                </div>
            </td>

            <td>
                <div class="line">
                    {{ $agentName ?: 'Agent' }}
                </div>
                <div class="small">
                    Received By / Agent
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        This voucher was generated by RPMCS and serves as proof of commission payment encoding.
    </div>
</body>
</html>
