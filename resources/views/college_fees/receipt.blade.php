<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Receipt - {{ $receipt->receipt_no }}</title>
    <link rel="stylesheet" href="{{ asset('assets/school/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 20px 0;
        }

        .receipt-container {
            width: 210mm;
            min-height: 148mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 24px 30px;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            position: relative;
            box-sizing: border-box;
            border: 1.5px solid #cbd5e1;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 75px;
            font-weight: 900;
            color: rgba(33, 115, 70, 0.08);
            text-transform: uppercase;
            pointer-events: none;
            white-space: nowrap;
            z-index: 9999;
        }

        .cancelled-watermark {
            position: absolute;
            top: 48%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 80px;
            font-weight: 900;
            color: rgba(220, 38, 38, 0.22);
            border: 4px dashed rgba(220, 38, 38, 0.28);
            padding: 6px 32px;
            border-radius: 10px;
            text-transform: uppercase;
            pointer-events: none;
            white-space: nowrap;
            z-index: 9999;
            letter-spacing: 5px;
        }

        .receipt-content {
            position: relative;
            z-index: 1;
        }

        .receipt-header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #217346;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .receipt-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            margin-right: 18px;
        }

        .institution-title {
            font-size: 20px;
            font-weight: 800;
            color: #1e3a8a;
            margin: 0 0 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .institution-sub {
            font-size: 11.5px;
            color: #475569;
            margin: 0;
            line-height: 1.4;
        }

        .receipt-badge {
            background: #217346;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 14px;
            font-size: 12px;
            border-collapse: collapse;
        }

        .info-grid td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .info-label {
            font-weight: 700;
            color: #475569;
            width: 15%;
        }

        .info-value {
            font-weight: 600;
            color: #0f172a;
            width: 35%;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 12.5px;
        }

        .items-table th {
            background: #f8fafc;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-weight: 700;
            color: #334155;
            text-align: left;
            text-transform: uppercase;
            font-size: 11px;
        }

        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .items-table tfoot td {
            font-weight: 800;
            border-top: 1.5px solid #0f172a;
            border-bottom: 1.5px solid #0f172a;
            padding: 8px 10px;
            font-size: 13px;
        }

        .action-bar {
            width: 210mm;
            margin: 0 auto 12px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .action-bar {
                display: none !important;
            }
            .receipt-container {
                box-shadow: none;
                border: 1px solid #94a3b8;
                width: 100%;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Print Action Bar -->
    <div class="action-bar">
        <a href="{{ route('college.fees.structures') }}" class="btn btn-outline-secondary btn-sm font-weight-bold">
            <i class="fa fa-arrow-left mr-1"></i> Back to Fee Matrix
        </a>
        <div class="d-flex" style="gap: 8px;">
            <a href="{{ route('college.fees.receipt_download', $receipt->id) }}" class="btn btn-primary btn-sm font-weight-bold" style="background: #1e3a8a; border: none; padding: 6px 16px;">
                <i class="fa fa-download mr-1"></i> Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-success btn-sm font-weight-bold" style="background: #217346; border: none; padding: 6px 16px;">
                <i class="fa fa-print mr-1"></i> Print Receipt (A4)
            </button>
        </div>
    </div>

    <!-- Printable Receipt Container -->
    <div class="receipt-container">
        
        @if($receipt->trashed())
            <div class="cancelled-watermark">CANCELLED</div>
        @else
            <div class="watermark">PAID</div>
        @endif

        <div class="receipt-content">
            
            @if($receipt->trashed())
                <div style="background: #fef2f2; border: 1.5px solid #ef4444; color: #b91c1c; padding: 7px 12px; border-radius: 4px; font-weight: 800; font-size: 13px; text-align: center; margin-bottom: 12px; letter-spacing: 0.5px;">
                    <i class="fa fa-ban mr-1"></i> THIS RECEIPT HAS BEEN CANCELLED (VOID)
                    @if($receipt->deleted_at)
                        &bull; Cancelled on {{ date('d M Y, h:i A', strtotime($receipt->deleted_at)) }}
                    @endif
                    @if(!empty($receipt->remark))
                        &bull; Reason: {{ $receipt->remark }}
                    @endif
                </div>
            @endif

            <!-- Header -->
            <div class="receipt-header">
                @if(!empty($setting->left_logo))
                    <img src="{{ env('IMAGE_SHOW_PATH').'/setting/left_logo/'.$setting->left_logo }}" class="receipt-logo" alt="Logo" onerror="this.style.display='none'">
                @endif
                <div style="flex-grow: 1;">
                    <h1 class="institution-title">{{ $setting->name ?? 'Shri Girraj Educational & Welfare Society' }}</h1>
                    <p class="institution-sub">
                        {{ $setting->address ?? 'Mathura, Uttar Pradesh' }} &bull; Ph: {{ $setting->mobile ?? '' }} &bull; Email: {{ $setting->gmail ?? '' }}
                    </p>
                </div>
                <div class="text-right">
                    @if($receipt->trashed())
                        <span class="receipt-badge" style="background: #dc2626;">Cancelled Receipt</span>
                    @else
                        <span class="receipt-badge">College Fee Receipt</span>
                    @endif
                    <div style="font-size: 11px; font-weight: 700; color: {{ $receipt->trashed() ? '#b91c1c' : '#166534' }}; margin-top: 4px;">
                        FY {{ date('Y', strtotime($receipt->payment_date)) }}-{{ substr((string)(date('Y', strtotime($receipt->payment_date)) + 1), -2) }}
                    </div>
                </div>
            </div>

            <!-- Receipt Meta Details -->
            <table class="info-grid">
                <tr>
                    <td class="info-label">Receipt No:</td>
                    <td class="info-value text-primary font-weight-bold" style="font-size: 13px;">{{ $receipt->receipt_no }}</td>
                    <td class="info-label">Payment Date:</td>
                    <td class="info-value">{{ date('d-M-Y', strtotime($receipt->payment_date)) }}</td>
                </tr>
                <tr>
                    <td class="info-label">Admission No:</td>
                    <td class="info-value font-weight-bold">{{ $student->admissionNo ?? 'N/A' }}</td>
                    <td class="info-label">Course & Sem:</td>
                    <td class="info-value">{{ $receipt->course->name ?? 'Course' }} &mdash; Semester {{ $receipt->semester_no }}</td>
                </tr>
                <tr>
                    <td class="info-label">Student Name:</td>
                    <td class="info-value font-weight-bold" style="text-transform: uppercase;">
                        {{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}
                    </td>
                    <td class="info-label">Batch:</td>
                    <td class="info-value">{{ $student->batch ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Father's Name:</td>
                    <td class="info-value">{{ $student->father_name ?? 'N/A' }}</td>
                    <td class="info-label">Payment Mode:</td>
                    <td class="info-value">
                        <span class="badge badge-light border font-weight-bold" style="font-size: 11px;">{{ $receipt->payment_mode }}</span>
                        @if($receipt->transaction_id)
                            <small class="text-muted ml-1">({{ $receipt->transaction_id }})</small>
                        @endif
                    </td>
                </tr>
            </table>

            <!-- Itemized Fee Heads Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Fee Head Description</th>
                        <th class="text-right" style="width: 140px;">Amount Allocated</th>
                    </tr>
                </thead>
                <tbody>
                    @php $subtotal = 0; @endphp
                    @forelse($receipt->items as $idx => $item)
                        @php $subtotal += $item->amount_allocated; @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong>{{ $item->head_name }}</strong></td>
                            <td class="text-right font-weight-bold">₹{{ number_format($item->amount_allocated, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td>1</td>
                            <td><strong>College Tuition & Term Fees</strong></td>
                            <td class="text-right font-weight-bold">₹{{ number_format($receipt->amount_paid, 2) }}</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-right">TOTAL AMOUNT RECEIVED:</td>
                        @if($receipt->trashed())
                            <td class="text-right text-danger" style="font-size: 15px;">
                                <del>₹{{ number_format($receipt->amount_paid, 2) }}</del> 
                                <span class="badge badge-danger ml-1" style="font-size: 10px; vertical-align: middle;">CANCELLED</span>
                            </td>
                        @else
                            <td class="text-right text-success" style="font-size: 15px;">₹{{ number_format($receipt->amount_paid, 2) }}</td>
                        @endif
                    </tr>
                </tfoot>
            </table>

            <!-- Amount in Words -->
            <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 4px; padding: 8px 12px; margin-bottom: 24px; font-size: 12px;">
                <strong>Amount in Words:</strong> <span style="font-style: italic; color: #1e293b;">INR {{ $amountInWords }} Only</span>
            </div>

            <!-- Signatures -->
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 36px; padding-top: 10px;">
                <div style="font-size: 11px; color: #64748b;">
                    <div>Cashier / Issued By: <strong>{{ $receipt->cashier->name ?? 'Accounts Desk' }}</strong></div>
                    <div>Generated: {{ date('d-M-Y h:i A') }}</div>
                    <div style="font-style: italic;">* Computer generated valid fee receipt. No physical stamp required.</div>
                </div>
                <div style="text-align: center; border-top: 1px solid #0f172a; width: 180px; padding-top: 4px;">
                    <strong style="font-size: 11.5px;">Authorized Signatory</strong>
                    <div style="font-size: 10.5px; color: #64748b;">Accounts Department</div>
                </div>
            </div>

        </div>

    </div>

    @if(request('auto_print'))
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 400);
        });
    </script>
    @endif
</body>
</html>
