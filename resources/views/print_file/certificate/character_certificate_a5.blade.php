@php
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

    $studentName = strtoupper(trim(preg_replace('/\s+/', ' ', trim($student->first_name ?? '') . ' ' . trim($student->last_name ?? ''))));
    $fatherName = strtoupper(trim(preg_replace('/\s+/', ' ', $student->father_name ?? '-')));
    $motherName = strtoupper(trim(preg_replace('/\s+/', ' ', $student->mother_name ?? '-')));
    $courseName = strtoupper(trim($student->ClassTypes->course_name ?? $student->course ?? 'PHARMACY'));
    $className = trim($student->ClassTypes->name ?? '');
    $batch = trim($student->batch ?? '');
    $admissionNo = trim($student->admissionNo ?? ('ADM-' . $student->id));
    $rollNo = trim($student->roll_no ?? '');

    $gender = strtolower($student->Gender->name ?? '');
    $genderPronoun = ($gender == 'female') ? 'Daughter' : 'Son';
    $possessivePronoun = ($gender == 'female') ? 'her' : 'his';
    $subjectPronoun = ($gender == 'female') ? 'she' : 'he';
    $objectivePronoun = ($gender == 'female') ? 'her' : 'him';

    $issueDate = !empty($cc_form->iessu_date) ? date('d-m-Y', strtotime($cc_form->iessu_date)) : date('d-m-Y');
    $certNo = 'SGL/CC/' . date('Y') . '/' . str_pad($student->id, 4, '0', STR_PAD_LEFT);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Character Certificate - {{ $studentName }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        /* Exact A5 Landscape specification */
        @page {
            size: A5 landscape;
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

        /* Top Action Bar for Browser View */
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
            font-size: 11.5px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .btn-print-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #217346;
            color: #ffffff;
            border: none;
            padding: 5px 14px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-print-action:hover {
            background: #185c37;
        }

        .btn-close-action {
            background: #475569;
            color: #ffffff;
            border: none;
            padding: 5px 12px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
        }

        /* Certificate A5 Landscape Container (210mm x 148mm) */
        /* Certificate A5 Landscape Container (210mm x 148mm) */
        .certificate-sheet {
            width: 210mm;
            height: 148mm;
            max-width: 210mm;
            max-height: 148mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 4.5mm 6.5mm;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Double Ornamental Certificate Border */
        .cert-outer-border {
            position: absolute;
            top: 3.5mm;
            left: 3.5mm;
            right: 3.5mm;
            bottom: 3.5mm;
            border: 2.5px solid #1e3a8a;
            pointer-events: none;
        }

        .cert-inner-border {
            position: absolute;
            top: 4.8mm;
            left: 4.8mm;
            right: 4.8mm;
            bottom: 4.8mm;
            border: 1px dashed #b45309;
            pointer-events: none;
        }

        /* Subtle Background Seal Watermark */
        .cert-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 72mm;
            height: 72mm;
            opacity: 0.045;
            pointer-events: none;
            object-fit: contain;
            z-index: 0;
        }

        .cert-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1mm 2.5mm 1mm 2.5mm;
        }

        /* Top Block */
        .cert-top-block {
            flex-shrink: 0;
        }

        /* Institutional Header */
        .cert-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px solid #1e3a8a;
            padding-bottom: 1.5mm;
            gap: 10px;
        }

        .cert-logo {
            width: 21mm;
            height: 21mm;
            object-fit: contain;
            flex-shrink: 0;
        }

        .cert-header-center {
            text-align: center;
            flex-grow: 1;
            padding: 0 4px;
        }

        .cert-society {
            font-size: 9.5pt;
            font-weight: 800;
            color: #b45309;
            letter-spacing: 1.3px;
            text-transform: uppercase;
            margin-bottom: 1.5px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .cert-college {
            font-size: 15.5pt;
            font-weight: 900;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 0 0 1.5px 0;
            line-height: 1.15;
        }

        .cert-address {
            font-size: 8pt;
            color: #334155;
            line-height: 1.3;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        /* Passport Size Photo Box (Physical Paste Area: 28mm x 34mm) */
        .cert-photo-box {
            width: 28mm;
            min-width: 28mm;
            height: 34mm;
            border: 1.5px dashed #475569;
            border-radius: 3px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            flex-shrink: 0;
            box-sizing: border-box;
            padding: 2px;
        }

        .cert-photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 2px;
        }

        .cert-photo-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            padding: 1px;
        }

        .cert-photo-icon {
            font-size: 11pt;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .cert-photo-text {
            font-size: 5.8pt;
            font-weight: 800;
            color: #334155;
            line-height: 1.25;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .cert-photo-sub {
            font-size: 5.2pt;
            font-weight: 600;
            color: #64748b;
            text-transform: none;
        }

        /* Meta Ribbon */
        .cert-meta-ribbon {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 8.8pt;
            font-weight: 600;
            color: #1e293b;
            padding: 1.2mm 1mm;
            border-bottom: 1px dotted #cbd5e1;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .cert-meta-ribbon strong {
            font-weight: 800;
            color: #0f172a;
        }

        .ribbon-item {
            display: inline-flex;
            align-items: center;
        }

        .ribbon-item.roll-col {
            justify-content: flex-end;
            min-width: 35%;
            text-align: right;
        }

        /* Certificate Title */
        .cert-title-wrap {
            text-align: center;
            margin: 2mm 0 1.5mm 0;
        }

        .cert-title {
            display: inline-block;
            font-size: 13.5pt;
            font-weight: 900;
            color: #1e3a8a;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border-bottom: 2px solid #b45309;
            padding-bottom: 1.5px;
        }

        /* Middle Block: Balanced, Filled Spacing Top and Bottom */
        .cert-body-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 1mm 4mm;
            margin: 0;
        }

        .cert-body {
            width: 100%;
            font-size: 10.6pt;
            line-height: 1.95;
            text-align: justify;
            color: #111827;
        }

        .cert-para {
            margin: 0 0 3.2mm 0;
            text-indent: 0;
        }

        .cert-para:last-child {
            margin-bottom: 0;
        }

        .cert-field {
            font-weight: 700;
            color: #0f172a;
            border-bottom: 1.2px solid #475569;
            padding: 0 2px;
            display: inline;
            font-family: 'Times New Roman', Georgia, serif;
        }

        .highlight-conduct {
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Footer & Signatures Block */
        .cert-footer {
            flex-shrink: 0;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding-top: 1.5mm;
            margin-top: 0;
            margin-bottom: 0.5mm;
        }

        .footer-left-col {
            text-align: left;
            width: 32%;
        }

        .cert-issue-meta {
            font-size: 8pt;
            line-height: 1.4;
            color: #1e293b;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin-bottom: 2.5mm;
        }

        .cert-issue-meta strong {
            font-weight: 800;
            color: #0f172a;
        }

        .footer-left-col .sig-space {
            height: 4mm;
        }

        .footer-left-col .sig-line {
            border-top: 1.2px solid #1e3a8a;
            width: 90%;
            margin: 0 0 2px 0;
        }

        .footer-left-col .sig-title {
            font-size: 8.2pt;
            font-weight: 700;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .footer-left-col .sig-sub {
            font-size: 6.8pt;
            color: #64748b;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .footer-seal-col {
            text-align: center;
            width: 32%;
        }

        .round-seal {
            width: 17mm;
            height: 17mm;
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
            line-height: 1.1;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .seal-caption {
            font-size: 6.8pt;
            font-weight: 700;
            color: #64748b;
            margin-top: 2px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .footer-right-col {
            text-align: center;
            width: 32%;
        }

        .footer-right-col .sig-space {
            height: 12mm;
        }

        .footer-right-col .sig-line {
            border-top: 1.2px solid #1e3a8a;
            width: 85%;
            margin: 0 auto 2px auto;
        }

        .footer-right-col .sig-title {
            font-size: 8.2pt;
            font-weight: 700;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .footer-right-col .sig-sub {
            font-size: 6.8pt;
            color: #64748b;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        @media print {
            @page {
                size: A5 landscape;
                margin: 0;
            }

            html, body {
                width: 210mm !important;
                height: 148mm !important;
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

            .certificate-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 4.5mm 6.5mm !important;
                width: 210mm !important;
                height: 148mm !important;
                min-height: 148mm !important;
                max-height: 148mm !important;
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
        <i class="fa fa-certificate mr-1 text-warning"></i> Character Certificate: <strong>{{ $studentName }}</strong> (A5 Landscape)
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn-print-action" onclick="window.print();">
            <i class="fa fa-print"></i> Print (A5 Landscape)
        </button>
        <button type="button" class="btn-close-action" onclick="window.close();">
            <i class="fa fa-times"></i> Close
        </button>
    </div>
</div>

<!-- Certificate Sheet (A5 Landscape) -->
<div class="certificate-sheet">
    <!-- Double Ornamental Frame -->
    <div class="cert-outer-border"></div>
    <div class="cert-inner-border"></div>

    <!-- Watermark Logo -->
    <img src="{{ $logoPath }}" class="cert-watermark" alt="Seal Watermark" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'/default/rukmani_logo.png' }}'">

    <div class="cert-content">
        <!-- 1. Top Section: Header, Ribbon, Title -->
        <div class="cert-top-block">
            <div class="cert-header">
                <img src="{{ $logoPath }}" class="cert-logo" alt="Emblem" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'/default/rukmani_logo.png' }}'">
                <div class="cert-header-center">
                    <div class="cert-society">{{ is_object($getSetting) ? ($getSetting->name ?? '') : ($getSetting['name'] ?? 'Shri Girraj Educational and Welfare Society') }}</div>
                    <h1 class="cert-college">{{ $institutionName }}</h1>
                    <div class="cert-address">
                        {{ $student->Branch->address ?? (is_object($getSetting) ? ($getSetting->address ?? '') : ($getSetting['address'] ?? 'Mathura, Uttar Pradesh')) }}<br>
                        <strong>Phone:</strong> {{ $student->Branch->phone ?? (is_object($getSetting) ? ($getSetting->mobile ?? '') : ($getSetting['mobile'] ?? '8791544882')) }} | 
                        <strong>Email:</strong> {{ $student->Branch->email ?? (is_object($getSetting) ? ($getSetting->gmail ?? '') : ($getSetting['gmail'] ?? 'info@girraj.org')) }}
                    </div>
                </div>
                <!-- Standard Passport Size Photo Area (30mm x 38mm) -->
                <div class="cert-photo-box">
                    @if(!empty($studentPhoto))
                        <img src="{{ $studentPhoto }}" alt="Student Photo" onerror="this.parentElement.innerHTML='<div class=\'cert-photo-inner\'><i class=\'fa fa-camera cert-photo-icon\'></i><div class=\'cert-photo-text\'>PASTE PASSPORT<br>SIZE PHOTO<br><span class=\'cert-photo-sub\'>(Self Attested)</span></div></div>';">
                    @else
                        <div class="cert-photo-inner">
                            <i class="fa fa-camera cert-photo-icon"></i>
                            <div class="cert-photo-text">
                                PASTE PASSPORT<br>SIZE PHOTO<br>
                                <span class="cert-photo-sub">(Self Attested)</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Metadata Ribbon (Without Date of Issue so Univ Roll No has plenty of dedicated room) -->
            <div class="cert-meta-ribbon">
                <span class="ribbon-item"><strong>Certificate No:</strong>&nbsp;{{ $certNo }}</span>
                <span class="ribbon-item"><strong>Admission No:</strong>&nbsp;{{ $admissionNo }}</span>
                <span class="ribbon-item roll-col"><strong>University Roll No:</strong>&nbsp;{{ !empty($rollNo) ? $rollNo : 'N/A' }}</span>
            </div>

            <!-- Certificate Title -->
            <div class="cert-title-wrap">
                <span class="cert-title">Character &amp; Conduct Certificate</span>
            </div>
        </div>

        <!-- 2. Middle Section: Balanced, Filled Spacing Top & Bottom -->
        <div class="cert-body-wrapper">
            <div class="cert-body">
                <p class="cert-para">
                    This is to certify that <span class="cert-field">{{ $studentName }}</span>, {{ $genderPronoun }} of Shri <span class="cert-field">{{ $fatherName }}</span> and Smt. <span class="cert-field">{{ $motherName }}</span>, has been a bonafide student of this institution in <span class="cert-field">{{ $courseName }}{{ !empty($className) ? " ($className)" : '' }}</span>, Batch <span class="cert-field">{{ $batch ?: '2024-2026' }}</span>, Academic Session <span class="cert-field">{{ $sessionYears }}</span>.
                </p>
                <p class="cert-para">
                    During {{ $possessivePronoun }} tenure of study at this institution, {{ $possessivePronoun }} conduct, character, and discipline have been found to be <span class="highlight-conduct">EXEMPLARY &amp; SATISFACTORY</span>. To the best of our knowledge and official institutional records, {{ $subjectPronoun }} bears an upright moral character and has not taken part in any activity detrimental to the discipline of the institution. We wish {{ $objectivePronoun }} abundant success in all future pursuits.
                </p>
            </div>
        </div>

        <!-- 3. Bottom Section: Issuance Metadata & Signatures Block -->
        <div class="cert-footer">
            <div class="footer-left-col">
                <div class="cert-issue-meta">
                    <div><strong>Date of Issue:</strong> {{ $issueDate }}</div>
                    <div><strong>Place:</strong> Mathura (U.P.)</div>
                </div>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-title">Prepared &amp; Checked By</div>
                <div class="sig-sub">Office Superintendent / Clerk</div>
            </div>

            <div class="footer-seal-col">
                @if(!empty($sealSignPath))
                    <div style="height: 18mm; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ $sealSignPath }}" alt="Seal" style="max-height: 18mm; max-width: 28mm; object-fit: contain;">
                    </div>
                @else
                    <div class="round-seal">
                        INSTITUTIONAL<br>OFFICIAL<br>SEAL
                    </div>
                @endif
                <div class="seal-caption">OFFICIAL SEAL</div>
            </div>

            <div class="footer-right-col">
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <div class="sig-title">Principal / Director</div>
                <div class="sig-sub">Authorized Administrator</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
