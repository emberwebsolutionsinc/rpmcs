<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Agent Commission Report</title>

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
            AGENT COMMISSION REPORT
        </div>

        <div class="generated">
            Generated: {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Agents</div>
                <div class="summary-value">{{ $summary['total_agents'] ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Sales</div>
                <div class="summary-value">{{ $summary['total_sales'] ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Contract Price</div>
                <div class="summary-value">
                    PHP {{ number_format($summary['total_contract_price'] ?? 0, 2) }}
                </div>
            </td>

            <td>
                <div class="summary-label">Gross Commission</div>
                <div class="summary-value">
                    PHP {{ number_format($summary['gross_commission'] ?? 0, 2) }}
                </div>
            </td>

            <td>
                <div class="summary-label">Paid</div>
                <div class="summary-value">
                    PHP {{ number_format($summary['paid_commission'] ?? 0, 2) }}
                </div>
            </td>

            <td>
                <div class="summary-label">Deleted</div>
                <div class="summary-value">
                    PHP {{ number_format($summary['deleted_commission'] ?? 0, 2) }}
                </div>
            </td>

            <td>
                <div class="summary-label">Unpaid</div>
                <div class="summary-value">
                    PHP {{ number_format($summary['unpaid_commission'] ?? 0, 2) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">
        Agent Summary
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Agent</th>
                <th>Code</th>
                <th>Type</th>
                <th class="center">Sales</th>
                <th class="right">Contract</th>
                <th class="center">Rate</th>
                <th class="right">Earned</th>
                <th class="right">Paid</th>
                <th class="right">Deleted</th>
                <th class="right">Balance</th>
            </tr>
        </thead>

        <tbody>
            @forelse($agents as $agent)
                <tr>
                    <td>{{ $agent['agent_name'] ?? '—' }}</td>
                    <td>{{ $agent['agent_code'] ?? '—' }}</td>
                    <td>{{ strtoupper(str_replace('_', ' ', $agent['agent_type'] ?? '—')) }}</td>
                    <td class="center">{{ $agent['sales_count'] ?? 0 }}</td>
                    <td class="right">{{ number_format($agent['total_contract_price'] ?? 0, 2) }}</td>
                    <td class="center">{{ number_format($agent['commission_rate'] ?? 0, 2) }}%</td>
                    <td class="right">{{ number_format($agent['commission_earned'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($agent['commission_paid'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($agent['commission_deleted'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($agent['commission_balance'] ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="center">
                        No agent commission records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">
        Commission Details Per Sale
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Sale No.</th>
                <th>Client</th>
                <th>Agent</th>
                <th>Lot</th>
                <th class="right">Contract</th>
                <th class="center">Rate</th>
                <th class="right">Earned</th>
                <th class="right">Paid</th>
                <th class="right">Deleted</th>
                <th class="right">Balance</th>
            </tr>
        </thead>

        <tbody>
            @forelse($sales as $sale)
                @php
                    $clientName = $sale->client
                        ? trim(($sale->client->first_name ?? '') . ' ' . ($sale->client->last_name ?? ''))
                        : '—';

                    $agentName = $sale->agent
                        ? trim(($sale->agent->first_name ?? '') . ' ' . ($sale->agent->last_name ?? ''))
                        : '—';

                    $rate = (float) ($sale->agent?->default_commission_rate ?? 0);
                    $commission = (float) $sale->contract_price * ($rate / 100);

                    $paid = \App\Models\AgentCommissionPayment::query()
                        ->where('sale_id', $sale->id)
                        ->sum('amount');

                    $deleted = \App\Models\AgentCommissionPayment::onlyTrashed()
                        ->where('sale_id', $sale->id)
                        ->sum('amount');
                @endphp

                <tr>
                    <td>{{ $sale->sale_no }}</td>
                    <td>{{ $clientName ?: '—' }}</td>
                    <td>{{ $agentName ?: '—' }}</td>
                    <td>{{ $sale->lot?->lot_no ?? '—' }}</td>
                    <td class="right">{{ number_format($sale->contract_price, 2) }}</td>
                    <td class="center">{{ number_format($rate, 2) }}%</td>
                    <td class="right">{{ number_format($commission, 2) }}</td>
                    <td class="right">{{ number_format($paid, 2) }}</td>
                    <td class="right">{{ number_format($deleted, 2) }}</td>
                    <td class="right">{{ number_format($commission - $paid, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="center">
                        No commissionable sales found.
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
            @forelse($deletedPayments ?? [] as $payment)
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
        This report was generated by RPMCS. Deleted commission payments are shown for audit trail purposes.
    </div>
</body>
</html>
