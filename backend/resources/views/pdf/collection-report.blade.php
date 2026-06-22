<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collections Report</title>
</head>
<body>
    <h2>RPMCS Collections Report</h2>

    <p>Generated: {{ now()->format('F d, Y h:i A') }}</p>

    <table border="1" cellspacing="0" cellpadding="5" width="100%">
        <thead>
            <tr>
                <th>Collection No.</th>
                <th>OR No.</th>
                <th>Client</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @foreach($collections as $collection)
                @php
                    $client = $collection->sale?->client;
                    $clientName = $client
                        ? trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''))
                        : '—';
                @endphp

                <tr>
                    <td>{{ $collection->collection_no }}</td>
                    <td>{{ $collection->official_receipt_no ?? '—' }}</td>
                    <td>{{ $clientName }}</td>
                    <td>{{ number_format($collection->amount_paid, 2) }}</td>
                    <td>{{ strtoupper(str_replace('_', ' ', $collection->payment_method)) }}</td>
                    <td>{{ optional($collection->payment_date)->format('Y-m-d') }}</td>
                    <td>{{ strtoupper($collection->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
