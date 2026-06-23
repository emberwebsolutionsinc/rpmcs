<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111;
        }

        h2, h4 {
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 15px;
            font-size: 10px;
        }

        .summary {
            margin-top: 10px;
            margin-bottom: 15px;
            width: 100%;
        }

        .summary td {
            padding: 4px;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
        }

        table.report th,
        table.report td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
        }

        table.report th {
            background-color: #eeeeee;
            font-weight: bold;
        }

        .amount {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .footer {
            margin-top: 20px;
            font-size: 9px;
            text-align: right;
        }
    </style>
</head>

<body>

<h2>REALTY SALES MANAGEMENT SYSTEM</h2>
<h4>Sales Report</h4>

<div class="subtitle">
    Generated on {{ now()->format('F d, Y h:i A') }}
</div>

<table class="summary">
    <tr>
        <td><strong>Date From:</strong> {{ $filters['date_from'] ?? 'All' }}</td>
        <td><strong>Date To:</strong> {{ $filters['date_to'] ?? 'All' }}</td>
        <td><strong>Status:</strong> {{ $filters['status'] ?? 'All' }}</td>
    </tr>
    <tr>
        <td><strong>Total Sales:</strong> {{ $summary['total_sales'] }}</td>
        <td><strong>Total Contract Price:</strong> ₱{{ number_format($summary['total_contract_price'], 2) }}</td>
        <td><strong>Total Commission:</strong> ₱{{ number_format($summary['total_commission'], 2) }}</td>
    </tr>
</table>

<table class="report">
    <thead>
        <tr>
            <th>#</th>
            <th>Sale Date</th>
            <th>Client</th>
            <th>Property</th>
            <th>Agent</th>
            <th>Total Contract Price</th>
            <th>Reservation Fee</th>
            <th>Downpayment</th>
            <th>Commission</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($sales as $index => $sale)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $sale->sale_date }}</td>
                <td>{{ $sale->client->name ?? 'N/A' }}</td>
                <td>{{ $sale->property->name ?? 'N/A' }}</td>
                <td>{{ $sale->agent->name ?? 'N/A' }}</td>
                <td class="amount">₱{{ number_format($sale->total_contract_price ?? 0, 2) }}</td>
                <td class="amount">₱{{ number_format($sale->reservation_fee ?? 0, 2) }}</td>
                <td class="amount">₱{{ number_format($sale->downpayment ?? 0, 2) }}</td>
                <td class="amount">₱{{ number_format($sale->commission_amount ?? 0, 2) }}</td>
                <td>{{ $sale->status ?? 'N/A' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" style="text-align:center;">No sales records found.</td>
            </tr>
        @endforelse

        <tr class="total-row">
            <td colspan="5">TOTAL</td>
            <td class="amount">₱{{ number_format($summary['total_contract_price'], 2) }}</td>
            <td class="amount">₱{{ number_format($summary['total_reservation_fee'], 2) }}</td>
            <td class="amount">₱{{ number_format($summary['total_downpayment'], 2) }}</td>
            <td class="amount">₱{{ number_format($summary['total_commission'], 2) }}</td>
            <td></td>
        </tr>
    </tbody>
</table>

<div class="footer">
    Realty Sales Management System / RPMCS
</div>

</body>
</html>
