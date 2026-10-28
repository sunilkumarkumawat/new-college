@php
    if (empty($student) && !empty($certificate_data) && !empty($certificate_data->admission_id)) {
        $student = App\Models\Admission::with(['ClassTypes', 'Gender', 'Branch'])->find($certificate_data->admission_id);
    }
    if (empty($student)) {
        $student = new \stdClass();
    }

    $getSetting = Helper::getSetting();
    $institutionName = $student->Branch->branch_name ?? (is_object($getSetting) ? ($getSetting->name ?? '') : ($getSetting['name'] ?? '')) ?: 'Shri Girraj Educational and Welfare Society';
    
    $leftLogo = is_object($getSetting) ? ($getSetting->left_logo ?? '') : ($getSetting['left_logo'] ?? '');
    $logoPath = !empty($leftLogo) 
        ? env('IMAGE_SHOW_PATH') . '/setting/left_logo/' . $leftLogo 
        : env('IMAGE_SHOW_PATH') . '/default/rukmani_logo.png';

    $sealSign = is_object($getSetting) ? ($getSetting->seal_sign ?? '') : ($getSetting['seal_sign'] ?? '');
    $sealSignPath = !empty($sealSign)
        ? env('IMAGE_SHOW_PATH') . '/setting/seal_sign/' . $sealSign
        : null;

    $studentPhoto = !empty($student->image)
        ? env('IMAGE_SHOW_PATH') . '/profile/' . $student->image
        : null;

    $session = DB::table('sessions')->whereNull('deleted_at')->where('id', $student->session_id ?? '')->first();
    $sessionYears = !empty($session) ? ($session->from_year . ' - ' . $session->to_year) : ($student->batch ?? date('Y'));

    $studentName = strtoupper(trim(($certificate_data->first_names ?? $student->first_name ?? '') . ' ' . ($certificate_data->last_name ?? $student->last_name ?? '')));
    $fatherName = strtoupper($certificate_data->admissions_father_name ?? $certificate_data->father_name ?? $student->father_name ?? '-');
    $motherName = strtoupper($certificate_data->admissions_mother_name ?? $certificate_data->mother_name ?? $student->mother_name ?? '-');
    $courseName = strtoupper($certificate_data->ClassName ?? $student->ClassTypes->course_name ?? $student->course ?? 'PHARMACY');
    $className = $student->ClassTypes->name ?? '';
    $batch = $student->batch ?? '';
    $admissionNo = $student->admissionNo ?? ('ADM-' . $student->id);
    $rollNo = $student->roll_no ?? '-';

    $gender = strtolower($student->Gender->name ?? '');
    $prefixStudent = ($gender == 'female') ? 'Miss' : 'Mr.';
    $genderPronoun = ($gender == 'female') ? 'Daughter' : 'Son';
    $possessivePronoun = ($gender == 'female') ? 'her' : 'his';
    $subjectPronoun = ($gender == 'female') ? 'she' : 'he';

    // TC Data Fallbacks
    $tcNo = !empty($certificate_data->id) 
        ? ('TC/' . date('Y') . '/' . str_pad($certificate_data->id, 4, '0', STR_PAD_LEFT))
        : ('TC/' . date('Y') . '/' . str_pad($student->id, 4, '0', STR_PAD_LEFT));

    $issueDate = !empty($certificate_data->issue_date) 
        ? date('d-m-Y', strtotime($certificate_data->issue_date)) 
        : date('d-m-Y');

    $admissionDate = !empty($certificate_data->admission_date) 
        ? date('d-m-Y', strtotime($certificate_data->admission_date)) 
        : (!empty($student->admission_date) ? date('d-m-Y', strtotime($student->admission_date)) : date('d-m-Y'));

    $dobRaw = $certificate_data->dob ?? $student->dob ?? null;
    $dobFormatted = !empty($dobRaw) ? date('d-m-Y', strtotime($dobRaw)) : '-';

    // Convert Date of Birth to Words
    if (!function_exists('tcNumberToWords')) {
        function tcNumberToWords($num) {
            $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 
                     'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
            $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
            
            $num = (int)$num;
            if ($num === 0) return 'Zero';
            if ($num < 20) return $ones[$num];
            if ($num < 100) return $tens[(int)($num / 10)] . ($num % 10 ? ' ' . $ones[$num % 10] : '');
            if ($num < 1000) return $ones[(int)($num / 100)] . ' Hundred' . ($num % 100 ? ' ' . tcNumberToWords($num % 100) : '');
            if ($num < 100000) return tcNumberToWords((int)($num / 1000)) . ' Thousand' . ($num % 1000 ? ' ' . tcNumberToWords($num % 1000) : '');
            return (string)$num;
        }
    }

    $dobInWords = '-';
    if (!empty($dobRaw) && strtotime($dobRaw)) {
        $timestamp = strtotime($dobRaw);
        $day = (int)date('j', $timestamp);
        $month = date('F', $timestamp);
        $year = (int)date('Y', $timestamp);

        $ordinals = [
            1 => 'First', 2 => 'Second', 3 => 'Third', 4 => 'Fourth', 5 => 'Fifth',
            6 => 'Sixth', 7 => 'Seventh', 8 => 'Eighth', 9 => 'Ninth', 10 => 'Tenth',
            11 => 'Eleventh', 12 => 'Twelfth', 13 => 'Thirteenth', 14 => 'Fourteenth', 15 => 'Fifteenth',
            16 => 'Sixteenth', 17 => 'Seventeenth', 18 => 'Eighteenth', 19 => 'Nineteenth', 20 => 'Twentieth',
            21 => 'Twenty-First', 22 => 'Twenty-Second', 23 => 'Twenty-Third', 24 => 'Twenty-Fourth', 25 => 'Twenty-Fifth',
            26 => 'Twenty-Sixth', 27 => 'Twenty-Seventh', 28 => 'Twenty-Eighth', 29 => 'Twenty-Ninth', 30 => 'Thirtieth', 31 => 'Thirty-First'
        ];

        $dayWord = $ordinals[$day] ?? tcNumberToWords($day);
        $yearWord = tcNumberToWords($year);
        $dobInWords = strtoupper($dayWord . ' ' . $month . ' ' . $yearWord);
    }

    $examResult = !empty($certificate_data->taken_result) ? strtoupper($certificate_data->taken_result) : 'PASSED & PROMOTED';
    $conductBehavior = !empty($certificate_data->conduct_behaviour) ? strtoupper($certificate_data->conduct_behaviour) : (!empty($certificate_data->behavior) ? strtoupper($certificate_data->behavior) : 'GOOD');
    $duesPaid = !empty($certificate_data->paid_school_dues) ? strtoupper($certificate_data->paid_school_dues) : 'CLEARED / ALL DUES PAID';
    $reasonLeaving = !empty($certificate_data->reasons_leaving) ? strtoupper($certificate_data->reasons_leaving) : 'COURSE COMPLETED / HIGHER STUDIES';
    $medium = !empty($certificate_data->mudium) ? strtoupper($certificate_data->mudium) : 'ENGLISH / HINDI';
    $subjects = !empty($certificate_data->subject) ? strtoupper($certificate_data->subject) : 'AS PER INSTITUTIONAL & BOARD / UNIVERSITY SYLLABUS';
    $remarks = !empty($certificate_data->any_remark) ? strtoupper($certificate_data->any_remark) : 'CHARACTER & CONDUCT FOUND SATISFACTORY';
    $scholarship = !empty($certificate_data->any_scholarship) ? strtoupper($certificate_data->any_scholarship) : 'NIL';
    $affiliationInfo = 'Approved by PCI / AICTE, New Delhi & Affiliated to BTE / AKTU / University';
    $branchAddress = $student->Branch->address ?? (is_object($getSetting) ? ($getSetting->address ?? '') : ($getSetting['address'] ?? 'Mathura, Uttar Pradesh'));
    $branchMobile = $student->Branch->phone ?? (is_object($getSetting) ? ($getSetting->mobile ?? '') : ($getSetting['mobile'] ?? '8791544882'));
    $branchEmail = $student->Branch->email ?? (is_object($getSetting) ? ($getSetting->gmail ?? '') : ($getSetting['gmail'] ?? 'info@girraj.org'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Certificate - {{ $studentName }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        /* Exact A4 Portrait specification */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Times New Roman', Times, Georgia, serif;
            color: #111827;
            background: #f1f5f9;
            margin: 0;
            padding: 15px 0;
        }

        /* Top Action Bar for Browser Preview */
        .no-print-toolbar {
            width: 210mm;
            margin: 0 auto 10px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #1e293b;
            color: #ffffff;
            padding: 7px 16px;
            border-radius: 4px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .btn-print-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 6px 15px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-print-action:hover {
            background: #1d4ed8;
        }

        .btn-close-action {
            background: #475569;
            color: #ffffff;
            border: none;
            padding: 6px 13px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
        }

        /* A4 Sheet Container (210mm x 297mm) */
        .tc-sheet {
            width: 210mm;
            min-height: 297mm;
            height: 297mm;
            max-height: 297mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 5mm 6.5mm;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }

        /* Double Ornamental Border */
        .tc-outer-border {
            position: absolute;
            top: 3.5mm;
            left: 3.5mm;
            right: 3.5mm;
            bottom: 3.5mm;
            border: 2.5px solid #1e3a8a;
            pointer-events: none;
        }

        .tc-inner-border {
            position: absolute;
            top: 4.8mm;
            left: 4.8mm;
            right: 4.8mm;
            bottom: 4.8mm;
            border: 1px dashed #b45309;
            pointer-events: none;
        }

        /* Subtle Centered Emblem Watermark */
        .tc-watermark {
            position: absolute;
            top: 52%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 110mm;
            height: 110mm;
            opacity: 0.045;
            pointer-events: none;
            object-fit: contain;
            z-index: 0;
        }

        .tc-content {
            position: relative;
            z-index: 1;
            height: 100%;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5mm 3mm 1mm 3mm;
            box-sizing: border-box;
        }

        .tc-top-block {
            flex-shrink: 0;
        }

        /* 1. Header Section */
        .tc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 2.5mm;
            gap: 12px;
        }

        .tc-logo {
            width: 22mm;
            height: 22mm;
            object-fit: contain;
            flex-shrink: 0;
        }

        .tc-header-center {
            text-align: center;
            flex-grow: 1;
        }

        .tc-society {
            font-size: 8.5pt;
            font-weight: 700;
            color: #b45309;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 1px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .tc-college {
            font-size: 15pt;
            font-weight: 900;
            color: #1e3a8a;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin: 0 0 1.5px 0;
            line-height: 1.15;
        }

        .tc-affiliation {
            font-size: 7.5pt;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1.5px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .tc-address {
            font-size: 7.2pt;
            color: #475569;
            line-height: 1.3;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        /* Photo Box (Standard Passport Photo: 30mm x 38mm) */
        .tc-photo-box {
            width: 30mm;
            min-width: 30mm;
            height: 38mm;
            border: 1.5px dashed #475569;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2px;
            flex-shrink: 0;
            background: #f8fafc;
            box-sizing: border-box;
            overflow: hidden;
        }

        .tc-photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 2px;
        }

        .tc-photo-label {
            font-size: 6.2pt;
            color: #475569;
            font-weight: 800;
            line-height: 1.25;
            text-transform: uppercase;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        /* 2. Certificate Title & Meta Bar */
        .tc-title-banner {
            text-align: center;
            margin: 4.5mm 0 3.5mm 0;
        }

        .tc-title {
            display: inline-block;
            font-size: 13.5pt;
            font-weight: 900;
            color: #1e3a8a;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            border-bottom: 2px solid #b45309;
            padding: 0 14px 2px 14px;
        }

        .tc-meta-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            padding: 1.8mm 3.5mm;
            font-size: 8.2pt;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2.5mm;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .tc-meta-bar span strong {
            color: #0f172a;
        }

        /* Table Middle Wrapper */
        .tc-table-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin: 1.5mm 0;
        }

        /* 3. Serialized Particulars Table (Standard 20 Items) */
        .tc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.1pt;
            line-height: 1.55;
        }

        .tc-table tr {
            border-bottom: 1px dotted #cbd5e1;
        }

        .tc-table tr:last-child {
            border-bottom: 1.5px solid #1e3a8a;
        }

        .tc-table td {
            padding: 5px 3px;
            vertical-align: middle;
        }

        .tc-col-sr {
            width: 22px;
            font-weight: 800;
            color: #1e3a8a;
            text-align: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 8pt;
        }

        .tc-col-label {
            width: 53%;
            font-weight: 600;
            color: #1e293b;
            padding-right: 6px;
        }

        .tc-col-colon {
            width: 10px;
            text-align: center;
            font-weight: bold;
            color: #475569;
        }

        .tc-col-val {
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.2px;
        }

        .tc-highlight-val {
            color: #1e3a8a;
            font-weight: 900;
        }

        /* Bottom Block */
        .tc-bottom-block {
            margin-top: auto;
            flex-shrink: 0;
        }

        /* 4. Official Signatures Block */
        .tc-signatures {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding-top: 3.5mm;
            margin-top: 1.5mm;
        }

        .tc-sig-col {
            text-align: center;
            width: 24%;
        }

        .tc-sig-space {
            height: 16mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .tc-sig-space img {
            max-height: 16mm;
            max-width: 32mm;
            object-fit: contain;
        }

        .tc-sig-line {
            border-top: 1.2px solid #1e293b;
            width: 85%;
            margin: 0 auto 2.5px auto;
        }

        .tc-sig-title {
            font-size: 8.2pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .tc-sig-sub {
            font-size: 6.8pt;
            color: #64748b;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .tc-seal-col {
            width: 22%;
            text-align: center;
        }

        .tc-seal-circle {
            width: 20mm;
            height: 20mm;
            border: 1.5px dashed #94a3b8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            color: #94a3b8;
            font-size: 6.8pt;
            font-weight: 700;
            text-align: center;
            line-height: 1.15;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            text-transform: uppercase;
        }

        /* 5. Legal Footnote */
        .tc-footnote {
            text-align: center;
            font-size: 6.8pt;
            color: #64748b;
            margin-top: 2mm;
            border-top: 1px dotted #cbd5e1;
            padding-top: 1.5mm;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            html, body {
                width: 210mm !important;
                height: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                overflow: hidden !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print-toolbar {
                display: none !important;
            }

            .tc-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 5mm 6.5mm !important;
                width: 210mm !important;
                height: 297mm !important;
                min-height: 297mm !important;
                max-height: 297mm !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                overflow: hidden !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            }
        }
    </style>
</head>
<body>

<!-- Browser Preview Controls -->
<div class="no-print-toolbar">
    <div>
        <i class="fa fa-graduation-cap mr-1 text-warning"></i> College Transfer Certificate: <strong>{{ $studentName }}</strong> (A4 Portrait)
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn-print-action" onclick="window.print();">
            <i class="fa fa-print"></i> Print (A4 Portrait)
        </button>
        <button type="button" class="btn-close-action" onclick="window.close();">
            <i class="fa fa-times"></i> Close
        </button>
    </div>
</div>

<!-- A4 Certificate Container Sheet -->
<div class="tc-sheet">
    <!-- Double Ornamental Frame -->
    <div class="tc-outer-border"></div>
    <div class="tc-inner-border"></div>

    <!-- Center Emblem Watermark -->
    <img src="{{ $logoPath }}" class="tc-watermark" alt="Emblem Watermark" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'/default/rukmani_logo.png' }}'">

    <div class="tc-content">
        <!-- Top Section: Header, Title & Metadata -->
        <div class="tc-top-block">
            <!-- 1. Institutional Header -->
            <div class="tc-header">
                <img src="{{ $logoPath }}" class="tc-logo" alt="College Logo" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'/default/rukmani_logo.png' }}'">
                
                <div class="tc-header-center">
                    <div class="tc-society">{{ is_object($getSetting) ? ($getSetting->name ?? '') : ($getSetting['name'] ?? 'Shri Girraj Educational and Welfare Society') }}</div>
                    <h1 class="tc-college">{{ $institutionName }}</h1>
                    <div class="tc-affiliation">{{ $affiliationInfo }}</div>
                    <div class="tc-address">
                        Campus: {{ $branchAddress }}<br>
                        <strong>Contact:</strong> {{ $branchMobile }} | 
                        <strong>Email:</strong> {{ $branchEmail }}
                    </div>
                </div>

                <!-- Student Passport Photo Box -->
                <div class="tc-photo-box">
                    @if(!empty($studentPhoto))
                        <img src="{{ $studentPhoto }}" alt="Student Photo" onerror="this.parentElement.innerHTML='<div class=\'tc-photo-label\'>Affix Passport<br>Photograph<br>Here</div>';">
                    @else
                        <div class="tc-photo-label">
                            Affix Passport<br>Photograph<br>Here
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. Certificate Title & Meta Ribbon -->
            <div class="tc-title-banner">
                <span class="tc-title">College Leaving / Transfer Certificate</span>
            </div>

            <div class="tc-meta-bar">
                <span><strong>T.C. Serial No:</strong> {{ $tcNo }}</span>
                <span><strong>Scholar / Admission No:</strong> {{ $admissionNo }}</span>
                <span><strong>Univ Roll No:</strong> {{ $rollNo }}</span>
                <span><strong>Date of Issue:</strong> {{ $issueDate }}</span>
            </div>
        </div>

        <!-- 3. Middle Section: Serialized 20 Particulars Table -->
        <div class="tc-table-wrapper">
            <table class="tc-table">
            <tbody>
                <tr>
                    <td class="tc-col-sr">1.</td>
                    <td class="tc-col-label">Name of the Student (in Block Letters)</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val tc-highlight-val">{{ $studentName }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">2.</td>
                    <td class="tc-col-label">Father's / Guardian's Name</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">{{ $fatherName }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">3.</td>
                    <td class="tc-col-label">Mother's Name</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">{{ $motherName }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">4.</td>
                    <td class="tc-col-label">Nationality &amp; Religion</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">INDIAN &nbsp;|&nbsp; {{ strtoupper($student->religion ?? 'HINDU') }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">5.</td>
                    <td class="tc-col-label">Category (Whether Candidate belongs to SC / ST / OBC / GEN / EWS)</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">{{ strtoupper($student->category ?? 'GENERAL') }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">6.</td>
                    <td class="tc-col-label">Date of First Admission to the College / Institute</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">{{ $admissionDate }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">7.</td>
                    <td class="tc-col-label">Class / Course in which first admitted</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">{{ $courseName }} {{ !empty($batch) ? "($batch Batch)" : '' }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">8.</td>
                    <td class="tc-col-label">
                        Date of Birth (according to Admission Register):<br>
                        &nbsp;&nbsp;&bull; In Figures &nbsp;&nbsp;&bull; In Words
                    </td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">
                        <span>{{ $dobFormatted }}</span><br>
                        <span style="font-size: 8pt; color: #1e3a8a;">{{ $dobInWords }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="tc-col-sr">9.</td>
                    <td class="tc-col-label">Class / Course in which the student last studied</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val tc-highlight-val">{{ $courseName }} {{ !empty($className) ? "($className)" : '' }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">10.</td>
                    <td class="tc-col-label">Board / University Annual Examination last taken with Result</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">{{ $examResult }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">11.</td>
                    <td class="tc-col-label">Whether failed, if so once / twice in the same class</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">{{ !empty($certificate_data->fail_pass) ? strtoupper($certificate_data->fail_pass) : 'NO' }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">12.</td>
                    <td class="tc-col-label">Medium of Instruction</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">{{ $medium }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">13.</td>
                    <td class="tc-col-label">Subjects Studied</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">{{ $subjects }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">14.</td>
                    <td class="tc-col-label">Whether qualified for promotion to higher class / course</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">{{ !empty($certificate_data->higher_class) ? strtoupper($certificate_data->higher_class) : 'YES (QUALIFIED FOR HIGHER STUDIES / DEGREE)' }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">15.</td>
                    <td class="tc-col-label">Month up to which the student has paid college dues / fees</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val tc-highlight-val">{{ $duesPaid }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">16.</td>
                    <td class="tc-col-label">Any fee concession availed of (if so, the nature of such concession)</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">{{ $scholarship }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">17.</td>
                    <td class="tc-col-label">Total No. of working days &amp; Student's Attendance record</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">SATISFACTORY (MET MINIMUM REQUIRED ATTENDANCE)</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">18.</td>
                    <td class="tc-col-label">General Conduct and Character</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val tc-highlight-val">{{ $conductBehavior }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">19.</td>
                    <td class="tc-col-label">Reason for leaving the College</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">{{ $reasonLeaving }}</td>
                </tr>
                <tr>
                    <td class="tc-col-sr">20.</td>
                    <td class="tc-col-label">Any other remarks / Academic standing</td>
                    <td class="tc-col-colon">:</td>
                    <td class="tc-col-val">{{ $remarks }}</td>
                </tr>
            </tbody>
        </table>
        </div>

        <!-- 4. Bottom Section: Signatures & Legal Footnote -->
        <div class="tc-bottom-block">
            <!-- Signatures Block -->
            <div class="tc-signatures">
                <div class="tc-sig-col">
                    <div class="tc-sig-space"></div>
                    <div class="tc-sig-line"></div>
                    <div class="tc-sig-title">Prepared By</div>
                    <div class="tc-sig-sub">Office Assistant / Clerk</div>
                </div>

                <div class="tc-sig-col">
                    <div class="tc-sig-space"></div>
                    <div class="tc-sig-line"></div>
                    <div class="tc-sig-title">Checked &amp; Verified By</div>
                    <div class="tc-sig-sub">Office Superintendent</div>
                </div>

                <div class="tc-seal-col">
                    @if(!empty($sealSignPath))
                        <div class="tc-sig-space">
                            <img src="{{ $sealSignPath }}" alt="Seal Stamp">
                        </div>
                    @else
                        <div class="tc-seal-circle">
                            COLLEGE<br>OFFICIAL<br>SEAL
                        </div>
                    @endif
                    <div style="font-size: 7.2pt; font-weight: 700; color: #475569; margin-top: 2px;">COLLEGE SEAL</div>
                </div>

                <div class="tc-sig-col">
                    <div class="tc-sig-space"></div>
                    <div class="tc-sig-line"></div>
                    <div class="tc-sig-title">Principal / Director</div>
                    <div class="tc-sig-sub">Authorized Signatory</div>
                </div>
            </div>

            <!-- 5. Legal Footnote -->
            <div class="tc-footnote">
                <strong>Notice:</strong> This Transfer Certificate is an official document issued under institutional authority. Any alteration, erasure, or unauthorized endorsement renders this certificate invalid.
            </div>
        </div>
    </div>
</div>

</body>
</html>
