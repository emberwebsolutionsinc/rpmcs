<table>
    <thead>
        <tr>
            <th colspan="9" style="font-weight: bold; font-size: 16px;">
                RPMCS COLLECTIONS REPORT
            </th>
        </tr>

        <tr>
            <th colspan="9">
                Generated: {{ now()->format('F d, Y h:i A') }}
            </th>
        </tr>

        <tr>
            <th colspan="9"></th>
        </tr>

        <tr>
            <th>Posted ORs</th>
            <th>Voided ORs</th>
            <th>Gross Collections</th>
            <th>Voided Amount</th>
            <th>Net Collections</th>
        </tr>

        <tr>
            <td>{{ $summary['posted_count'] }}</td>
            <td>{{ $summary['voided_count'] }}</td>
            <td>{{ number_format($summary['gross_collections'], 2) }}</td>
            <td>{{ number_format($summary['voided_amount'], 2) }}</td>
            <td>{{ number_format($summary['net_collections'], 2) }}</td>
        </tr>

        <tr>
            <th colspan="9"></th>
        </tr>

        <tr>
            <th>Collection No.</th>
            <th>OR No.</th>
            <th>Client</th>
            <th>Project</th>
            <th>Lot</th>
            <th>Amount Paid</th>
            <th>Payment Method</th>
            <th>Payment Date</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
        @foreach($collections as $collection)
            @php
                $client = $collection->sale?->client;

                $clientName = $client
                    ? trim(
                        ($client->first_name ?? '') . ' ' .
                        ($client->middle_name ?? '') . ' ' .
                        ($client->last_name ?? '')
                    )
                    : '—';
            @endphp

            <tr>
                <td>{{ $collection->collection_no }}</td>
                <td>{{ $collection->official_receipt_no ?? '—' }}</td>
                <td>{{ $clientName ?: '—' }}</td>
                <td>{{ $collection->sale?->lot?->project?->project_name ?? '—' }}</td>
                <td>{{ $collection->sale?->lot?->lot_no ?? '—' }}</td>
                <td>{{ number_format($collection->amount_paid, 2) }}</td>
                <td>{{ strtoupper(str_replace('_', ' ', $collection->payment_method)) }}</td>
                <td>{{ optional($collection->payment_date)->format('Y-m-d') }}</td>
                <td>{{ strtoupper($collection->status) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
