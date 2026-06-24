<table>
    <thead>
        <tr>
            <th colspan="12">RPMCS AGENT COMMISSION REPORT</th>
        </tr>

```
    <tr>
        <th colspan="12">
            Generated:
            {{ now()->format('F d, Y h:i A') }}
        </th>
    </tr>

    <tr></tr>

    <tr>
        <th>Total Agents</th>
        <th>Total Sales</th>
        <th>Total Contract Price</th>
        <th>Total Downpayment</th>
        <th>Total Balance</th>
        <th>Gross Commission</th>
        <th>Paid Commission</th>
        <th>Deleted Commission</th>
        <th>Unpaid Commission</th>
    </tr>

    <tr>
        <td>{{ $summary['total_agents'] ?? 0 }}</td>
        <td>{{ $summary['total_sales'] ?? 0 }}</td>
        <td>{{ $summary['total_contract_price'] ?? 0 }}</td>
        <td>{{ $summary['total_downpayment'] ?? 0 }}</td>
        <td>{{ $summary['total_balance'] ?? 0 }}</td>
        <td>{{ $summary['gross_commission'] ?? 0 }}</td>
        <td>{{ $summary['paid_commission'] ?? 0 }}</td>
        <td>{{ $summary['deleted_commission'] ?? 0 }}</td>
        <td>{{ $summary['unpaid_commission'] ?? 0 }}</td>
    </tr>

    <tr></tr>
    <tr>
        <th colspan="12">AGENT SUMMARY</th>
    </tr>

    <tr>
        <th>Agent Code</th>
        <th>Agent Name</th>
        <th>Agent Type</th>
        <th>Sales Count</th>
        <th>Contract Price</th>
        <th>Downpayment</th>
        <th>Balance</th>
        <th>Rate</th>
        <th>Commission Earned</th>
        <th>Commission Paid</th>
        <th>Deleted Commission</th>
        <th>Commission Balance</th>
    </tr>
</thead>

<tbody>
    @foreach($agents as $agent)
        <tr>
            <td>{{ $agent['agent_code'] }}</td>
            <td>{{ $agent['agent_name'] }}</td>
            <td>{{ $agent['agent_type'] }}</td>
            <td>{{ $agent['sales_count'] }}</td>
            <td>{{ $agent['total_contract_price'] }}</td>
            <td>{{ $agent['total_downpayment'] }}</td>
            <td>{{ $agent['total_balance'] }}</td>
            <td>{{ $agent['commission_rate'] }}%</td>
            <td>{{ $agent['commission_earned'] }}</td>
            <td>{{ $agent['commission_paid'] }}</td>
            <td>{{ $agent['commission_deleted'] }}</td>
            <td>{{ $agent['commission_balance'] }}</td>
        </tr>
    @endforeach

    <tr></tr>
    <tr>
        <th colspan="12">COMMISSION DETAILS PER SALE</th>
    </tr>

    <tr>
        <th>Sale No.</th>
        <th>Client</th>
        <th>Agent</th>
        <th>Project</th>
        <th>Lot</th>
        <th>Contract Price</th>
        <th>Downpayment</th>
        <th>Balance</th>
        <th>Rate</th>
        <th>Commission Earned</th>
        <th>Commission Paid</th>
        <th>Commission Balance</th>
    </tr>

    @foreach($sales as $sale)
        <tr>
            <td>{{ $sale->sale_no }}</td>

            <td>
                {{ trim(($sale->client->first_name ?? '') . ' ' . ($sale->client->last_name ?? '')) }}
            </td>

            <td>
                {{ trim(($sale->agent->first_name ?? '') . ' ' . ($sale->agent->last_name ?? '')) }}
            </td>

            <td>{{ $sale->lot?->project?->project_name ?? '—' }}</td>
            <td>{{ $sale->lot?->lot_no ?? '—' }}</td>

            <td>{{ $sale->contract_price }}</td>
            <td>{{ $sale->downpayment }}</td>
            <td>{{ $sale->balance }}</td>

            <td>{{ $sale->commission_rate ?? 0 }}%</td>

            <td>{{ $sale->commission_earned ?? 0 }}</td>
            <td>{{ $sale->commission_paid ?? 0 }}</td>
            <td>{{ $sale->commission_balance ?? 0 }}</td>
        </tr>
    @endforeach

    <tr></tr>
    <tr>
        <th colspan="12">
            DELETED / VOIDED COMMISSION PAYMENTS
        </th>
    </tr>

    <tr>
        <th>Deleted At</th>
        <th>Agent</th>
        <th>Sale No.</th>
        <th>Client</th>
        <th>Amount</th>
        <th>Reason</th>
        <th>Deleted By</th>
    </tr>

    @foreach($deletedPayments ?? [] as $payment)
        <tr>
            <td>
                {{ optional($payment->deleted_at)->format('Y-m-d H:i') }}
            </td>

            <td>
                {{ trim(($payment->agent->first_name ?? '') . ' ' . ($payment->agent->last_name ?? '')) }}
            </td>

            <td>
                {{ $payment->sale?->sale_no ?? '—' }}
            </td>

            <td>
                {{ trim(($payment->sale?->client?->first_name ?? '') . ' ' . ($payment->sale?->client?->last_name ?? '')) }}
            </td>

            <td>
                {{ $payment->amount }}
            </td>

            <td>
                {{ $payment->delete_reason ?? '—' }}
            </td>

            <td>
                {{ $payment->deletedBy?->name ?? $payment->deletedBy?->email ?? '—' }}
            </td>
        </tr>
    @endforeach
</tbody>
```

</table>
