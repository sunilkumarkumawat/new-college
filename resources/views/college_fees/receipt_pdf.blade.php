@php
    $paymentYear = date('Y', strtotime($receipt->payment_date));
    $paymentMonth = date('n', strtotime($receipt->payment_date));
    $fyString = ($paymentMonth >= 4)
        ? 'FY ' . $paymentYear . '-' . substr((string)($paymentYear + 1), -2)
        : 'FY ' . ($paymentYear - 1) . '-' . substr((string)$paymentYear, -2);

    $totalItems = count($receipt->items);
    $minRows = $receipt->trashed() ? 4 : 5;
    $emptyRowsNeeded = max(0, $minRows - ($totalItems > 0 ? $totalItems : 1));
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fee Receipt - {{ $receipt->receipt_no }}</title>
    <style>
                @page {
            size: a5 landscape;
            margin: 4mm 4mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: "DejaVu Sans", "Helvetica Neue", Arial, sans-serif;
            font-size: 9.5px;
            color: #0f172a;
            margin: 0;
            padding: 0;
            line-height: 1.25;
        }
        .receipt-container {
            border: 2px solid #217346;
            padding: 7px 12px;
            height: 135mm;
            margin: 0;
            position: relative;
            background: #ffffff;
            box-sizing: border-box;
            page-break-inside: avoid;
        }
        .watermark {
            position: absolute;
            top: 40%;
            left: 37%;
            transform: rotate(-15deg);
            font-size: 72px;
            font-weight: bold;
            color: rgba(33, 115, 70, 0.08);
            text-transform: uppercase;
            z-index: 9999;
            pointer-events: none;
            white-space: nowrap;
        }
        .cancelled-watermark-pdf {
            position: absolute;
            top: 36%;
            left: 18.5%;
            transform: rotate(-15deg);
            font-size: 56px;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.22);
            border: 3.5px dashed rgba(220, 38, 38, 0.28);
            padding: 6px 30px;
            border-radius: 8px;
            text-transform: uppercase;
            z-index: 9999;
            letter-spacing: 5px;
            text-align: center;
            white-space: nowrap;
            pointer-events: none;
        }
        .content-layer {
            position: relative;
            z-index: 1;
        }
        
        /* Header Table */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #217346;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .inst-title {
            font-size: 15px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 0 0 2px 0;
        }
        .inst-sub {
            font-size: 8.5px;
            color: #475569;
            margin: 0;
            line-height: 1.3;
        }
        .badge-receipt {
            background-color: #217346;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            padding: 4px 14px;
            border-radius: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            white-space: nowrap;
        }
        .receipt-fy {
            font-size: 9px;
            font-weight: bold;
            color: #166534;
            margin-top: 3px;
        }

        /* Student & Meta Info Grid */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .info-grid td {
            padding: 3px 6px;
            font-size: 9.5px;
            border: 1px solid #e2e8f0;
        }
        .info-lbl {
            font-weight: bold;
            color: #475569;
            width: 15%;
            background: #f1f5f9;
        }
        .info-val {
            color: #0f172a;
            width: 35%;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .items-table th {
            background: #e7eff5;
            color: #1e3a5f;
            border-top: 1.5px solid #217346;
            border-bottom: 1.5px solid #217346;
            padding: 3px 6px;
            font-size: 9.5px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 4px 6px;
            font-size: 9.5px;
            height: 18px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }

        /* Words Box */
        .words-box {
            background: #fafafa;
            border: 1px dashed #cbd5e1;
            padding: 4px 6px;
            margin-bottom: 8px;
            font-size: 9px;
        }

        /* Signatures Table */
        .sign-table {
            width: 100%;
            margin-top: 4px;
        }
        .sign-table td {
            vertical-align: bottom;
            font-size: 8.5px;
        }
        .seal-space {
            height: 70px;
        }
        .sign-line {
            border-top: 1.2px solid #0f172a;
            width: 175px;
            margin-left: auto;
            text-align: center;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <div class="receipt-container" style="{{ $receipt->trashed() ? 'border-color: #ef4444;' : '' }}">
        
        <div class="content-layer">
            
            @if($receipt->trashed())
                <div style="background: #fef2f2; border: 1px solid #ef4444; color: #b91c1c; padding: 3px 6px; border-radius: 2px; font-weight: bold; font-size: 8.5px; text-align: center; margin-bottom: 5px;">
                    THIS RECEIPT HAS BEEN CANCELLED (VOID)
                    @if($receipt->deleted_at)
                        &bull; Cancelled on {{ date('d-M-Y h:i A', strtotime($receipt->deleted_at)) }}
                    @endif
                    @if(!empty($receipt->remark))
                        &bull; Reason: {{ $receipt->remark }}
                    @endif
                </div>
            @endif

            <!-- Header -->
            <table class="header-table" style="{{ $receipt->trashed() ? 'border-bottom-color: #ef4444;' : '' }}">
                <tr>
                    <td style="width: 72%;">
                        <div class="inst-title">{{ $setting->name ?? 'Shri Girraj Educational & Welfare Society' }}</div>
                        <div class="inst-sub">
                            {{ $setting->address ?? 'Mathura, Uttar Pradesh' }} &bull; Ph: {{ $setting->mobile ?? '' }} &bull; Email: {{ $setting->gmail ?? '' }}
                        </div>
                    </td>
                    <td style="width: 28%; text-align: right;">
                        @if($receipt->trashed())
                            <div class="badge-receipt" style="background-color: #dc2626;">Cancelled (VOID)</div>
                        @else
                            <div class="badge-receipt">Fee Receipt</div>
                        @endif
                        <div class="receipt-fy" style="color: {{ $receipt->trashed() ? '#b91c1c' : '#166534' }};">
                            {{ $fyString }}
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Meta Details -->
            <table class="info-grid">
                <tr>
                    <td class="info-lbl">Receipt No:</td>
                    <td class="info-val bold" style="color: #1e3a8a;">{{ $receipt->receipt_no }}</td>
                    <td class="info-lbl">Payment Date:</td>
                    <td class="info-val bold">{{ date('d-M-Y', strtotime($receipt->payment_date)) }}</td>
                </tr>
                <tr>
                    <td class="info-lbl">Admission No:</td>
                    <td class="info-val bold">{{ $student->admissionNo ?? 'N/A' }}</td>
                    <td class="info-lbl">Course & Sem:</td>
                    <td class="info-val">{{ $receipt->course->name ?? 'Course' }} &mdash; Semester {{ $receipt->semester_no }}</td>
                </tr>
                <tr>
                    <td class="info-lbl">Student Name:</td>
                    <td class="info-val bold" style="text-transform: uppercase;">
                        {{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}
                    </td>
                    <td class="info-lbl">Batch:</td>
                    <td class="info-val">{{ $student->batch ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-lbl">Father's Name:</td>
                    <td class="info-val">{{ $student->father_name ?? 'N/A' }}</td>
                    <td class="info-lbl">Payment Mode:</td>
                    <td class="info-val bold">
                        {{ $receipt->payment_mode }}
                        @if($receipt->transaction_id)
                            <span style="font-size: 8px; color: #64748b; font-weight: normal;">({{ $receipt->transaction_id }})</span>
                        @endif
                    </td>
                </tr>
            </table>

            <!-- Items Table (5 rows reserved) -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 32px;" class="text-center">#</th>
                        <th>Fee Head Description</th>
                        <th class="text-right" style="width: 140px;">Amount Allocated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipt->items as $idx => $item)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="bold">{{ $item->head_name }}</td>
                            <td class="text-right bold">&#8377;{{ number_format($item->amount_allocated, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center">1</td>
                            <td class="bold">College Tuition &amp; Term Fees</td>
                            <td class="text-right bold">&#8377;{{ number_format($receipt->amount_paid, 2) }}</td>
                        </tr>
                    @endforelse
                    
                    @for($i = 0; $i < $emptyRowsNeeded; $i++)
                        <tr class="empty-row">
                            <td class="text-center">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor

                    <!-- Total Row -->
                    @if($receipt->trashed())
                    <tr style="border-top: 1.5px solid #dc2626; border-bottom: 1.5px solid #dc2626; background: #fef2f2;">
                        <td colspan="2" class="text-right bold" style="font-size: 10px; padding: 3px 6px; color: #991b1b;">TOTAL AMOUNT (VOID):</td>
                        <td class="text-right bold" style="color: #dc2626; font-size: 11px; padding: 3px 6px;">
                            <del>&#8377;{{ number_format($receipt->amount_paid, 2) }}</del> <span style="font-size: 8px;">(CANCELLED)</span>
                        </td>
                    </tr>
                    @else
                    <tr style="border-top: 1.5px solid #217346; border-bottom: 1.5px solid #217346; background: #f0fdf4;">
                        <td colspan="2" class="text-right bold" style="font-size: 10px; padding: 3px 6px;">TOTAL AMOUNT RECEIVED:</td>
                        <td class="text-right bold" style="color: #166534; font-size: 11.5px; padding: 3px 6px;">&#8377;{{ number_format($receipt->amount_paid, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <!-- Amount in Words -->
            <div class="words-box">
                <strong>Amount in Words:</strong> <em style="color: #1e293b;">INR {{ $amountInWords }} Only</em>
            </div>

            <!-- Signatures & Seal Section -->
            <table class="sign-table">
                <tr>
                    <td style="width: 58%; color: #64748b;">
                        <div>Cashier / Issued By: <strong style="color: #0f172a;">{{ $receipt->cashier->name ?? 'Accounts Desk' }}</strong></div>
                        <div style="margin-top: 2px;">Generated: {{ date('d-M-Y h:i A') }}</div>
                        <div style="font-style: italic; margin-top: 3px; color: #475569;">* Official college fee receipt. Verified and signed by authorized authority.</div>
                    </td>
                    <td style="width: 42%; text-align: right;">
                        <div class="seal-space"></div>
                        <div class="sign-line">
                            <div class="bold" style="color: #0f172a; font-size: 9.5px;">Authorized Signatory</div>
                            <div style="color: #64748b; font-size: 8.5px;">Accounts Department / Seal</div>
                        </div>
                    </td>
                </tr>
            </table>

        </div>
            <!-- Watermark positioned with highest z-index so it stays visible over tables -->
        @if($receipt->trashed())
            <div class="cancelled-watermark-pdf">CANCELLED</div>
        @else
            <div class="watermark">PAID</div>
        @endif
    </div>
</body>
</html>
