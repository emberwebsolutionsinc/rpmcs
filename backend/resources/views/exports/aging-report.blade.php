<table>
    <thead>
        <tr>
            <th colspan="12" style="font-weight: bold; font-size: 16px;">
                RPMCS AGING REPORT
            </th>
        </tr>

        <tr>
            <th colspan="12">
                Generated: {{ now()->format('F d, Y h:i A') }}
            </th>
        </tr>

        <tr>
            <th colspan="12"></th>
        </tr>

        <tr>
            <th>Total Accounts</th>
            <th>Total Installments</th>
            <th>Total Balance</th>
            <th>Current Amount</th>
            <th>Overdue Amount</th>
            <th>Critical Amount</th>
        </tr>

        <tr>
            <td>{{ $summary['total_accounts'] }}</td>
            <td>{{ $summary['total_installments'] }}</td>
            <td>{{ number_format($summary['total_balance'], 2) }}</td>
            <td>{{ number_format($summary['current_amount'], 2) }}</td>
            <td>{{ number_format($summary['overdue_amount'], 2) }}</td>
            <td>{{ number_format($summary['critical_amount'], 2) }}</td>
        </tr>

        <tr>
            <th colspan="12"></th>
        </tr>

        <tr>
            <th>Bucket</th>
            <th>Count</th>
            <th>Amount</th>
        </tr>

        @foreach($buckets as $bucket)
            <tr>
                <td>{{ $bucket['label'] }}</td>
                <td>{{ $bucket['count'] }}</td>
                <td>{{ number_format($bucket['amount'], 2) }}</td>
            </tr>
        @endforeach

        <tr>
            <th colspan="12"></th>
        </tr>

        <tr>
            <th>Client</th>
            <th>Sale No.</th>
            <th>Agent</th>
            <th>Project</th>
            <th>Lot</th>
            <th>Installment</th>
            <th>Due Date</th>
            <th>Days Late</th>
            <th>Amount Due</th>
            <th>Amount Paid</th>
            <th>Balance</th>
            <th>Aging Bucket</th>
        </tr>
    </thead>

    <tbody>
        @foreach($records as $record)
            @php
                $client = $record->sale?->client;

                $clientName = $client
                    ? trim(
                        ($client->first_name ?? '') . ' ' .
                        ($client->middle_name ?? '') . ' ' .
                        ($client->last_name ?? '')
                    )
                    : '—';

                $agent = $record->sale?->agent;

                $agentName = $agent
                    ? trim(
                        ($agent->first_name ?? '') . ' ' .
                        ($agent->middle_name ?? '') . ' ' .
                        ($agent->last_name ?? '')
                    )
                    : '—';
            @endphp

            <tr>
                <td>{{ $clientName ?: '—' }}</td>
                <td>{{ $record->sale?->sale_no ?? '—' }}</td>
                <td>{{ $agentName ?: '—' }}</td>
                <td>{{ $record->sale?->lot?->project?->project_name ?? '—' }}</td>
                <td>{{ $record->sale?->lot?->lot_no ?? '—' }}</td>
                <td>{{ $record->installment_no }}</td>
                <td>{{ optional($record->due_date)->format('Y-m-d') }}</td>
                <td>{{ $record->days_late }}</td>
                <td>{{ number_format($record->amount_due, 2) }}</td>
                <td>{{ number_format($record->amount_paid, 2) }}</td>
                <td>{{ number_format($record->balance, 2) }}</td>
                <td>{{ $record->aging_bucket }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
