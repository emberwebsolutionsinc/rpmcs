<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Official Receipt</title>

    <style>
        @page {
            margin: 14px;
        }

        body {
         font-family: Courier, monospace;
            font-size: 10px;
            color: #0f172a;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        .receipt {
            position: relative;
            border: 1.5px solid #0f172a;
            padding: 18px;
        }

        .top-border {
            height: 6px;
            background: #047857;
            margin: -18px -18px 14px -18px;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        .watermark {
            position: absolute;
            top: 38%;
            left: 10%;
            transform: rotate(-25deg);
            font-size: 70px;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.12);
            z-index: 0;
        }

        .header {
            text-align: center;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .company {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: .8px;
            color: #064e3b;
        }

        .company-subtitle {
            margin-top: 2px;
            font-size: 9px;
            color: #475569;
        }

        .receipt-title {
            margin-top: 8px;
            display: inline-block;
            border: 1.5px solid #047857;
            color: #047857;
            padding: 5px 14px;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: .8px;
        }

        .or-box {
            margin-bottom: 8px;
            text-align: right;
        }

        .or-label {
            font-size: 8.5px;
            color: #64748b;
            text-transform: uppercase;
        }

        .or-number {
            margin-top: 2px;
            font-size: 14px;
            font-weight: bold;
            color: #dc2626;
        }

        .section {
            margin-top: 8px;
            border: 1px solid #e2e8f0;
            padding: 8px;
        }

        .section-title {
            font-size: 9px;
            font-weight: bold;
            color: #047857;
            text-transform: uppercase;
            margin-bottom: 5px;
            letter-spacing: .4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding: 3px 4px;
        }

        .label {
            font-size: 8.5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .2px;
        }

        .value {
            margin-top: 1px;
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
        }

        .value-normal {
            margin-top: 1px;
            font-size: 10px;
            color: #334155;
        }

        .amount-box {
            margin-top: 10px;
            border: 1.5px solid #047857;
            background: #ecfdf5;
            padding: 10px;
            text-align: center;
        }

        .amount-label {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            color: #047857;
        }

        .amount {
            margin-top: 3px;
            font-size: 22px;
            font-weight: bold;
            color: #064e3b;
        }

        .payment-method {
            margin-top: 4px;
            font-size: 10px;
        }

        .remarks {
            margin-top: 8px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 7px;
        }

        .signature-row {
            margin-top: 34px;
            width: 100%;
        }

        .signature-box {
            float: right;
            width: 38%;
            text-align: center;
            border-top: 1px solid #0f172a;
            padding-top: 5px;
            font-size: 9px;
            color: #334155;
        }

        .footer-note {
            clear: both;
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 8px;
            color: #64748b;
            text-align: center;
        }

        .status-posted {
            color: #047857;
        }

        .status-voided {
            color: #dc2626;
        }
    </style>
</head>

<body>
    @php
        $sale = $collection->sale;
        $client = $sale?->client;
        $lot = $sale?->lot;
        $project = $lot?->project;
        $schedule = $collection->paymentSchedule;

        $clientName = $client
            ? trim(
                ($client->first_name ?? '') . ' ' .
                ($client->middle_name ?? '') . ' ' .
                ($client->last_name ?? '')
            )
            : '—';

        $statusClass = $collection->status === 'voided'
            ? 'status-voided'
            : 'status-posted';
    @endphp

    <div class="receipt">
        @if($collection->status === 'voided')
            <div class="watermark">VOIDED</div>
        @endif

        <div class="content">
            <div class="top-border"></div>

            <div class="header">
                <div class="company">
                    BANTOG REALTY PROPERTY MANAGEMENT
                </div>

                <div class="company-subtitle">
                    Real Property Management and Collection System
                </div>

                <div class="receipt-title">
                    OFFICIAL RECEIPT
                </div>
            </div>

            <div class="or-box">
                <div class="or-label">Official Receipt No.</div>
                <div class="or-number">
                    {{ $collection->official_receipt_no ?? '—' }}
                </div>
            </div>

            <div class="section">
                <div class="section-title">Receipt Information</div>

                <table>
                    <tr>
                        <td>
                            <div class="label">Collection Number</div>
                            <div class="value">{{ $collection->collection_no }}</div>
                        </td>

                        <td>
                            <div class="label">Payment Date</div>
                            <div class="value">
                                {{ optional($collection->payment_date)->format('F d, Y') }}
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div class="label">Status</div>
                            <div class="value {{ $statusClass }}">
                                {{ strtoupper($collection->status) }}
                            </div>
                        </td>

                        <td>
                            <div class="label">Reference No.</div>
                            <div class="value">
                                {{ $collection->reference_no ?? '—' }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Received From</div>

                <table>
                    <tr>
                        <td>
                            <div class="label">Client Name</div>
                            <div class="value">{{ $clientName ?: '—' }}</div>
                        </td>

                        <td>
                            <div class="label">Client Code</div>
                            <div class="value">
                                {{ $client?->client_code ?? '—' }}
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div class="label">Contact Number</div>
                            <div class="value">
                                {{ $client?->contact_number ?? '—' }}
                            </div>
                        </td>

                        <td>
                            <div class="label">Email</div>
                            <div class="value">
                                {{ $client?->email ?? '—' }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Property / Sale Details</div>

                <table>
                    <tr>
                        <td>
                            <div class="label">Sale Number</div>
                            <div class="value">{{ $sale?->sale_no ?? '—' }}</div>
                        </td>

                        <td>
                            <div class="label">Project</div>
                            <div class="value">{{ $project?->project_name ?? '—' }}</div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div class="label">Lot</div>
                            <div class="value">{{ $lot?->lot_no ?? '—' }}</div>
                        </td>

                        <td>
                            <div class="label">Installment</div>
                            <div class="value">
                                {{ $schedule?->installment_no ? 'Installment #' . $schedule->installment_no : '—' }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="amount-box">
                <div class="amount-label">
                    Amount Received
                </div>

                <div class="amount">
                    PHP {{ number_format($collection->amount_paid, 2) }}
                </div>

                <div class="payment-method">
                    <span class="label">Payment Method:</span>
                    <strong>
                        {{ strtoupper(str_replace('_', ' ', $collection->payment_method)) }}
                    </strong>
                </div>
            </div>

            <div class="remarks">
                <div class="label">Remarks</div>
                <div class="value-normal">
                    {{ $collection->remarks ?? 'No remarks.' }}
                </div>
            </div>

            <div class="signature-row">
                <div class="signature-box">
                    Authorized Representative
                </div>
            </div>

            <div class="footer-note">
                This receipt was generated by RPMCS. This receipt is valid only if the collection status is POSTED.
                <br>
                For more information, please contact the system administrator.
                <br>
                <strong>Thank you!</strong>
            </div>
            Powered by: Ember Web Solutionds Inc. 2026
            </div>
        </div>
    </div>
</body>
</html>
