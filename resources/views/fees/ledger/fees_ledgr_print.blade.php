@php
    $getSetting = Helper::getSetting();
    $logoPath = !empty($getSetting->left_logo) 
        ? env('IMAGE_SHOW_PATH') . '/setting/left_logo/' . $getSetting->left_logo 
        : env('IMAGE_SHOW_PATH') . '/default/rukmani_logo.png';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fees Ledger Statement - {{ $student[0]->first_name ?? 'Student' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #0f172a;
            background: #f8fafc;
            margin: 0;
            padding: 15px 0;
        }

        .ledger-sheet {
            background: #ffffff;
            width: 100%;
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto 20px auto;
            padding: 12mm 14mm;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #cbd5e1;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Toolbar for Browser Preview */
        .no-print-toolbar {
            max-width: 210mm;
            margin: 0 auto 12px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #1e293b;
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 4px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .btn-print-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #217346;
            color: #ffffff;
            border: none;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-print-action:hover {
            background: #185c37;
            color: #ffffff;
        }

        .btn-close-action {
            background: #475569;
            color: #ffffff;
            border: none;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-close-action:hover {
            background: #334155;
            color: #ffffff;
        }

        /* Institutional Header */
        .header-wrap {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #217346;
            padding-bottom: 8px;
            margin-bottom: 10px;
            gap: 14px;
        }

        .header-logo {
            width: 75px;
            height: 75px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .header-info {
            flex-grow: 1;
            text-align: center;
        }

        .society-name {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 2px;
        }

        .college-name {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 0 0 3px 0;
            line-height: 1.2;
        }

        .college-meta {
            font-size: 10px;
            color: #475569;
            line-height: 1.35;
        }

        /* Statement Banner */
        .statement-banner {
            background: #217346;
            color: #ffffff;
            text-align: center;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            border-radius: 2px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Student Profile Card */
        .profile-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #cbd5e1;
            background: #fdfefe;
        }

        .profile-table td {
            padding: 4px 8px;
            font-size: 10.5px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .profile-table .lbl {
            font-weight: 700;
            color: #334155;
            background: #f8fafc;
            width: 15%;
        }

        .profile-table .val {
            color: #0f172a;
            font-weight: 600;
            width: 35%;
        }

        /* Ledger Transactions Table */
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10px;
        }

        .ledger-table th {
            background: #217346;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 5px 6px;
            border: 1px solid #185c37;
            text-align: left;
        }

        .ledger-table th.num, .ledger-table td.num {
            text-align: right;
        }

        .ledger-table th.center, .ledger-table td.center {
            text-align: center;
        }

        .ledger-table td {
            padding: 4.5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        .ledger-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .badge-type {
            font-size: 9px;
            font-weight: 700;
            padding: 1px 4px;
            border-radius: 2px;
            text-transform: uppercase;
            display: inline-block;
        }

        .badge-dr {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .badge-cr {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        /* Financial Summary Box */
        .summary-wrap {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .summary-notes {
            flex: 1;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            padding: 8px 10px;
            background: #f8fafc;
            font-size: 9.5px;
            color: #475569;
            line-height: 1.45;
        }

        .summary-card {
            width: 250px;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            overflow: hidden;
            background: #ffffff;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
        }

        .summary-row .s-lbl {
            color: #475569;
            font-weight: 600;
        }

        .summary-row .s-val {
            font-weight: 700;
            color: #0f172a;
        }

        .summary-row.total-due {
            background: #eef6f0;
            border-bottom: none;
            padding: 6px 8px;
        }

        .summary-row.total-due .s-lbl {
            font-size: 11px;
            color: #185c37;
            font-weight: 800;
        }

        .summary-row.total-due .s-val {
            font-size: 12px;
            color: #217346;
            font-weight: 800;
        }

        /* Signatures Section */
        .sign-wrap {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 25px;
            margin-top: 10px;
            border-top: 1px dashed #cbd5e1;
        }

        .sign-col {
            text-align: center;
            width: 28%;
        }

        .sign-line {
            border-top: 1px solid #334155;
            margin-bottom: 4px;
        }

        .sign-title {
            font-size: 10px;
            font-weight: 700;
            color: #1e293b;
        }

        .sign-sub {
            font-size: 8.5px;
            color: #64748b;
        }

        .seal-box {
            width: 80px;
            height: 80px;
            border: 1px dashed #94a3b8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 6px auto;
            color: #94a3b8;
            font-size: 9px;
            font-weight: 600;
            text-align: center;
            line-height: 1.1;
        }

        /* Print Specific CSS */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .no-print, .no-print-toolbar {
                display: none !important;
            }

            .ledger-sheet {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
                width: 100%;
                min-height: auto;
            }

            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

@if(!empty($student) && count($student) > 0)
@foreach ($student as $data)
@php
    $institutionName = $data->Branch->branch_name ?? $getSetting->name ?? 'Shri Girraj Educational and Welfare Society';
    $session = DB::table('sessions')->whereNull('deleted_at')->where('id', $data->session_id ?? '')->first();
    $sessionText = !empty($session) ? ($session->from_year . ' - ' . $session->to_year) : ($data->batch ?? 'Current');
    
    // Fetch Assigned Fee Heads (Debits)
    $feesAssign = DB::table('fees_assign_details')
        ->leftJoin('fees_group', 'fees_group.id', '=', 'fees_assign_details.fees_group_id')
        ->where('fees_assign_details.admission_id', $data->id)
        ->whereNull('fees_assign_details.deleted_at')
        ->select('fees_assign_details.*', 'fees_group.name as group_name')
        ->orderBy('fees_assign_details.created_at', 'ASC')
        ->get();

    // Fetch Receipts / Invoices (Credits)
    $invoices = DB::table('fees_details_invoices')
        ->where('admission_id', $data->id)
        ->whereIn('status', [0, 1])
        ->orderBy('payment_date', 'ASC')
        ->get();

    // Combine transactions into unified chronologically sorted ledger
    $transactions = [];

    foreach ($feesAssign as $assign) {
        $gross = (float)($assign->fees_group_amount ?? $assign->total_amount ?? 0);
        $disc = (float)($assign->discount ?? 0);
        $netDr = max(0, $gross - $disc);

        $transactions[] = (object)[
            'date' => !empty($assign->created_at) ? date('Y-m-d', strtotime($assign->created_at)) : date('Y-m-d'),
            'type' => 'DR',
            'particulars' => $assign->group_name ?? 'Tuition Fee Head',
            'ref_no' => 'HEAD-' . $assign->id,
            'debit' => $netDr,
            'credit' => 0,
            'discount' => $disc,
            'gross' => $gross
        ];
    }

    foreach ($invoices as $inv) {
        $crAmount = (float)($inv->amount ?? 0);
        $transactions[] = (object)[
            'date' => !empty($inv->payment_date) ? date('Y-m-d', strtotime($inv->payment_date)) : date('Y-m-d'),
            'type' => 'CR',
            'particulars' => 'Fee Payment Received' . (!empty($inv->payment_mode) ? ' (' . $inv->payment_mode . ')' : ''),
            'ref_no' => $inv->invoice_no ?? ('RCPT-' . $inv->id),
            'debit' => 0,
            'credit' => $crAmount,
            'discount' => (float)($inv->discount ?? 0),
            'gross' => $crAmount
        ];
    }

    // Sort transactions by date ASC
    usort($transactions, function($a, $b) {
        return strcmp($a->date, $b->date);
    });

    $totalGross = $feesAssign->sum(function($item) { return $item->fees_group_amount ?? $item->total_amount ?? 0; });
    $totalDiscount = $feesAssign->sum('discount');
    $netAssignedDr = max(0, $totalGross - $totalDiscount);
    $totalPaidCr = $invoices->sum('amount');
    $netBalanceDue = $netAssignedDr - $totalPaidCr;
@endphp

<!-- Browser Floating Action Bar -->
<div class="no-print-toolbar">
    <div style="font-weight: 600; font-size: 12.5px;">
        <i class="fa fa-file-text-o mr-1"></i> Student Fee Ledger: <strong>{{ $data->first_name }} {{ $data->last_name ?? '' }}</strong> (ID: {{ $data->admissionNo ?? $data->id }})
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn-print-action" onclick="window.print();">
            <i class="fa fa-print"></i> Print Statement
        </button>
        <button type="button" class="btn-close-action" onclick="window.close();">
            <i class="fa fa-times"></i> Close
        </button>
    </div>
</div>

<div class="ledger-sheet">
    <div>
        <!-- Institutional Header -->
        <div class="header-wrap">
            <img src="{{ $logoPath }}" class="header-logo" alt="College Logo" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'/default/rukmani_logo.png' }}'">
            <div class="header-info">
                <div class="society-name">{{ $getSetting->name ?? 'Shri Girraj Educational and Welfare Society' }}</div>
                <h1 class="college-name">{{ $institutionName }}</h1>
                <div class="college-meta">
                    {{ $data->Branch->address ?? $getSetting->address ?? 'Mathura, Uttar Pradesh' }}<br>
                    <strong>Contact:</strong> {{ $data->Branch->phone ?? $getSetting->mobile ?? '8791544882' }} | 
                    <strong>Email:</strong> {{ $data->Branch->email ?? $getSetting->gmail ?? 'info@girraj.org' }}
                </div>
            </div>
            <div style="width: 75px; text-align: right;" class="no-print">
                <span style="font-size: 9px; font-weight: 700; color: #217346; border: 1px solid #217346; padding: 2px 4px; border-radius: 2px;">OFFICIAL</span>
            </div>
        </div>

        <!-- Statement Title Banner -->
        <div class="statement-banner">
            <span>OFFICIAL STUDENT FEES LEDGER STATEMENT</span>
            <span>Date: {{ date('d-M-Y') }}</span>
        </div>

        <!-- Student Profile Table -->
        <table class="profile-table">
            <tr>
                <td class="lbl">Student Name:</td>
                <td class="val">{{ $data->first_name }} {{ $data->last_name ?? '' }}</td>
                <td class="lbl">Scholar / Adm No:</td>
                <td class="val">{{ $data->admissionNo ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Father's Name:</td>
                <td class="val">{{ $data->father_name ?? '-' }}</td>
                <td class="lbl">Univ Roll No:</td>
                <td class="val">{{ $data->roll_no ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Course / Stream:</td>
                <td class="val">{{ $data->ClassTypes->course_name ?? $data->course ?? '-' }}</td>
                <td class="lbl">Class / Semester:</td>
                <td class="val">{{ $data->class_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Batch / Session:</td>
                <td class="val">{{ $data->batch ?? '-' }} ({{ $sessionText }})</td>
                <td class="lbl">Student Mobile:</td>
                <td class="val">{{ $data->mobile ?? '-' }}</td>
            </tr>
        </table>

        <!-- Ledger Table -->
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width: 32px;" class="center">#</th>
                    <th style="width: 80px;" class="center">Date</th>
                    <th>Particulars / Transaction Details</th>
                    <th style="width: 100px;">Ref / Receipt No</th>
                    <th style="width: 85px;" class="num">Debit (Dr ₹)</th>
                    <th style="width: 85px;" class="num">Credit (Cr ₹)</th>
                    <th style="width: 95px;" class="num">Balance (₹)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $runningBalance = 0;
                    $idx = 1;
                @endphp

                @if(count($transactions) > 0)
                    @foreach ($transactions as $txn)
                        @php
                            if ($txn->type === 'DR') {
                                $runningBalance += $txn->debit;
                            } else {
                                $runningBalance -= $txn->credit;
                            }
                        @endphp
                        <tr>
                            <td class="center">{{ $idx++ }}</td>
                            <td class="center">{{ date('d-m-Y', strtotime($txn->date)) }}</td>
                            <td>
                                <strong>{{ $txn->particulars }}</strong>
                                @if($txn->type === 'DR' && $txn->discount > 0)
                                    <small style="color: #64748b; display: block;">(Gross: ₹{{ number_format($txn->gross, 2) }}, Concession: ₹{{ number_format($txn->discount, 2) }})</small>
                                @endif
                            </td>
                            <td><code>{{ $txn->ref_no }}</code></td>
                            <td class="num font-weight-bold" style="color: {{ $txn->debit > 0 ? '#991b1b' : '#64748b' }};">
                                {{ $txn->debit > 0 ? number_format($txn->debit, 2) : '-' }}
                            </td>
                            <td class="num font-weight-bold" style="color: {{ $txn->credit > 0 ? '#166534' : '#64748b' }};">
                                {{ $txn->credit > 0 ? number_format($txn->credit, 2) : '-' }}
                            </td>
                            <td class="num" style="font-weight: 700; color: {{ $runningBalance > 0 ? '#991b1b' : ($runningBalance < 0 ? '#166534' : '#0f172a') }};">
                                ₹{{ number_format(abs($runningBalance), 2) }} {{ $runningBalance > 0 ? 'Dr' : ($runningBalance < 0 ? 'Cr (Adv)' : '') }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="center" style="padding: 20px; color: #64748b;">
                            <i class="fa fa-info-circle mr-1"></i> No fee assignment or payment transactions recorded for this student yet. Account balance is Nil.
                        </td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: 700;">
                    <td colspan="4" style="text-align: right; padding-right: 10px;">Total Summary:</td>
                    <td class="num" style="color: #991b1b;">₹{{ number_format($netAssignedDr, 2) }}</td>
                    <td class="num" style="color: #166534;">₹{{ number_format($totalPaidCr, 2) }}</td>
                    <td class="num" style="color: {{ $netBalanceDue > 0 ? '#991b1b' : '#166534' }};">
                        ₹{{ number_format(abs($netBalanceDue), 2) }} {{ $netBalanceDue > 0 ? 'Dr' : ($netBalanceDue < 0 ? 'Cr' : '') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Summary Wrap -->
        <div class="summary-wrap">
            <div class="summary-notes">
                <strong>Terms & Accounting Disclosures:</strong>
                <ul style="margin: 4px 0 0 14px; padding: 0;">
                    <li>All entries are recorded as per official institution books and fee schedules.</li>
                    <li>Receipt numbers must be quoted for any discrepancies or reconciliation.</li>
                    <li>This statement reflects transactions recorded up to <strong>{{ date('d-M-Y H:i') }}</strong>.</li>
                    <li>Cheque/draft payments are subject to realization.</li>
                </ul>
            </div>

            <div class="summary-card">
                <div class="summary-row">
                    <span class="s-lbl">Gross Assigned:</span>
                    <span class="s-val">₹{{ number_format($totalGross, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="s-lbl">Concession / Disc:</span>
                    <span class="s-val" style="color: #166534;">₹{{ number_format($totalDiscount, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="s-lbl">Net Payable (Dr):</span>
                    <span class="s-val">₹{{ number_format($netAssignedDr, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="s-lbl">Total Paid (Cr):</span>
                    <span class="s-val" style="color: #166534;">₹{{ number_format($totalPaidCr, 2) }}</span>
                </div>
                <div class="summary-row total-due">
                    <span class="s-lbl">{{ $netBalanceDue >= 0 ? 'Balance Due:' : 'Advance Balance:' }}</span>
                    <span class="s-val">₹{{ number_format(abs($netBalanceDue), 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Official Signatures Block -->
    <div class="sign-wrap">
        <div class="sign-col">
            <div class="sign-line"></div>
            <div class="sign-title">Prepared By</div>
            <div class="sign-sub">Cashier / Fee Clerk</div>
        </div>

        <div class="sign-col">
            <div class="seal-box">
                OFFICIAL<br>COLLEGE<br>SEAL
            </div>
            <div class="sign-title">Institutional Stamp</div>
        </div>

        <div class="sign-col">
            <div class="sign-line"></div>
            <div class="sign-title">Accountant / Principal</div>
            <div class="sign-sub">Authorized Signatory</div>
        </div>
    </div>
</div>

@if(!$loop->last)
    <div class="page-break"></div>
@endif

@endforeach
@else
<div style="text-align: center; padding: 50px; font-family: sans-serif;">
    <h3>Student record not found for fee ledger statement.</h3>
    <a href="javascript:history.back()" style="color: #217346;">Go Back</a>
</div>
@endif

</body>
</html>