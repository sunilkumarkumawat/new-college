@extends('layout.app')
@section('content')

@php
    $sidebarSubList = Helper::SidebarSubPerm(9) ?? [];
    $isHindi = Session::get('locale') == 'hi';

    // Master module metadata mapping with Excel Green & Executive accents
    $metaMap = [
        'course_add' => [
            'title_en' => 'Courses & Programs',
            'title_hi' => 'पाठ्यक्रम एवं कोर्स',
            'desc' => 'Degree programs, academic courses & department streams',
            'desc_hi' => 'डिग्री प्रोग्राम, शैक्षणिक पाठ्यक्रम एवं स्ट्रीम सेटअप',
            'icon' => 'fa-graduation-cap',
            'color' => '#217346',
            'bg_tint' => '#f0fdf4',
            'category' => 'academic'
        ],
        'add_class' => [
            'title_en' => 'Class / Semester',
            'title_hi' => 'कक्षा / सेमेस्टर',
            'desc' => 'Academic classes, sections & semester setup',
            'desc_hi' => 'शैक्षणिक कक्षाएं, अनुभाग एवं सेमेस्टर प्रबंधन',
            'icon' => 'fa-university',
            'color' => '#0284c7',
            'bg_tint' => '#f0f9ff',
            'category' => 'academic'
        ],
        'create_subject' => [
            'title_en' => 'Add Subject',
            'title_hi' => 'विषय मास्टर',
            'desc' => 'Curriculum course subjects, codes & paper types',
            'desc_hi' => 'कोर्स विषय, पेपर कोड एवं क्रेडिट प्रबंधन',
            'icon' => 'fa-book',
            'color' => '#059669',
            'bg_tint' => '#ecfdf5',
            'category' => 'academic'
        ],
        'add_subject' => [
            'title_en' => 'Assign Subject',
            'title_hi' => 'विषय आवंटन',
            'desc' => 'Map curriculum subjects to classes & faculty',
            'desc_hi' => 'कक्षाओं एवं शिक्षकों को विषय आवंटित करें',
            'icon' => 'fa-bookmark-o',
            'color' => '#0891b2',
            'bg_tint' => '#ecfeff',
            'category' => 'academic'
        ],
        'teacher_subject_add' => [
            'title_en' => 'Classes Timetable',
            'title_hi' => 'समय सारणी (टाइमटेबल)',
            'desc' => 'Master weekly timetable & faculty period schedules',
            'desc_hi' => 'कक्षावार साप्ताहिक समय सारणी एवं शिक्षक आवंटन',
            'icon' => 'fa-table',
            'color' => '#4f46e5',
            'bg_tint' => '#eef2ff',
            'category' => 'academic'
        ],
        'time_periods' => [
            'title_en' => 'Time Periods',
            'title_hi' => 'समय अवधि (पीरियड्स)',
            'desc' => 'Lecture bell timings, lecture slots & duration',
            'desc_hi' => 'घंटी का समय, पीरियड स्लॉट एवं अंतराल सेटअप',
            'icon' => 'fa-clock-o',
            'color' => '#d97706',
            'bg_tint' => '#fffbeb',
            'category' => 'academic'
        ],
        'add_weekend' => [
            'title_en' => 'Academic Calendar',
            'title_hi' => 'शैक्षणिक कैलेंडर',
            'desc' => 'Institutional calendar, gazetted holidays & weekly offs',
            'desc_hi' => 'संस्थान का वार्षिक कैलेंडर, अवकाश एवं साप्ताहिक छुट्टियां',
            'icon' => 'fa-calendar-check-o',
            'color' => '#ea580c',
            'bg_tint' => '#fff7ed',
            'category' => 'academic'
        ],
        'session_add' => [
            'title_en' => 'Academic Sessions',
            'title_hi' => 'शैक्षणिक सत्र',
            'desc' => 'Academic financial years & active session switch',
            'desc_hi' => 'शैक्षणिक वर्ष, नया सत्र निर्माण एवं सत्र चयन',
            'icon' => 'fa-calendar',
            'color' => '#b91c1c',
            'bg_tint' => '#fef2f2',
            'category' => 'academic'
        ],
        'viewBranch' => [
            'title_en' => 'Campus / Branch',
            'title_hi' => 'शाखा / कैंपस',
            'desc' => 'Campus branches, location profile & institute settings',
            'desc_hi' => 'कैंपस शाखाएं, स्थान विवरण एवं संस्थान विवरण',
            'icon' => 'fa-building',
            'color' => '#0284c7',
            'bg_tint' => '#f0f9ff',
            'category' => 'admin'
        ],
        'role_add' => [
            'title_en' => 'Roles & Permissions',
            'title_hi' => 'भूमिका एवं अधिकार (रोल)',
            'desc' => 'User roles, permission matrix & staff access control',
            'desc_hi' => 'स्टाफ भूमिकाएं, अनुमति मैट्रिक्स एवं सुरक्षा नियंत्रण',
            'icon' => 'fa-shield',
            'color' => '#475569',
            'bg_tint' => '#f8fafc',
            'category' => 'admin'
        ],
        'enquiry_status_add' => [
            'title_en' => 'Enquiry Status',
            'title_hi' => 'पूछताछ स्थिति (लीड)',
            'desc' => 'Admission inquiry stages, pipeline tags & lead status',
            'desc_hi' => 'प्रवेश पूछताछ चरण, पाइपलाइन टैग एवं लीड स्थिति',
            'icon' => 'fa-tags',
            'color' => '#2563eb',
            'bg_tint' => '#eff6ff',
            'category' => 'admin'
        ],
        'notice_board/view/0' => [
            'title_en' => 'Notice Board',
            'title_hi' => 'सूचना पट्ट (नोटिस बोर्ड)',
            'desc' => 'Publish campus notices, circulars & student bulletins',
            'desc_hi' => 'संस्थान परिपत्र, छात्र सूचनाएं एवं घोषणाएं',
            'icon' => 'fa-bullhorn',
            'color' => '#7c3aed',
            'bg_tint' => '#faf5ff',
            'category' => 'communication'
        ],
        'messageDashboard' => [
            'title_en' => 'Message Templates',
            'title_hi' => 'संदेश टेम्पलेट',
            'desc' => 'SMS, WhatsApp & system automated communication templates',
            'desc_hi' => 'एसएमएस, व्हाट्सएप एवं स्वचालित सूचना टेम्पलेट',
            'icon' => 'fa-comments-o',
            'color' => '#16a34a',
            'bg_tint' => '#f0fdf4',
            'category' => 'communication'
        ],
        'whatsapp_setting' => [
            'title_en' => 'WhatsApp Setting',
            'title_hi' => 'व्हाट्सएप सेटिंग्स',
            'desc' => 'WhatsApp Business Cloud API, gateway keys & webhook config',
            'desc_hi' => 'व्हाट्सएप क्लाउड एपीआई, गेटवे कुंजी एवं वेबहुक सेटिंग्स',
            'icon' => 'fa-whatsapp',
            'color' => '#15803d',
            'bg_tint' => '#f0fdf4',
            'category' => 'communication'
        ],
        'complaint_view' => [
            'title_en' => 'Complaints & Grievances',
            'title_hi' => 'शिकायत निवारण',
            'desc' => 'Student & parent grievances, support tickets & redressal',
            'desc_hi' => 'विद्यार्थी व अभिभावक शिकायतें एवं निवारण ट्रैकिंग',
            'icon' => 'fa-exclamation-circle',
            'color' => '#e11d48',
            'bg_tint' => '#fff1f2',
            'category' => 'desk'
        ],
        'leave' => [
            'title_en' => 'Staff Leave Master',
            'title_hi' => 'अवकाश प्रबंधन',
            'desc' => 'Staff leave types, annual allowances & approval rules',
            'desc_hi' => 'कर्मचारी अवकाश के प्रकार, वार्षिक कोटा एवं नियम',
            'icon' => 'fa-bed',
            'color' => '#ca8a04',
            'bg_tint' => '#fefce8',
            'category' => 'admin'
        ],
        'gate_pass_view' => [
            'title_en' => 'Gate Pass Security',
            'title_hi' => 'गेट पास प्रबंधन',
            'desc' => 'Visitor campus entry, student out-passes & OTP validation',
            'desc_hi' => 'आगंतुक प्रवेश, विद्यार्थी आउट-पास एवं ओटीपी सत्यापन',
            'icon' => 'fa-id-card-o',
            'color' => '#0284c7',
            'bg_tint' => '#f0f9ff',
            'category' => 'desk'
        ],
        'school_desk' => [
            'title_en' => 'School Desk',
            'title_hi' => 'स्कूल डेस्क / फ्रंट ऑफिस',
            'desc' => 'Front-office inquiries, reception logging & public desk',
            'desc_hi' => 'फ्रंट-ऑफिस पूछताछ, रिसेप्शन रजिस्टर एवं आगंतुक डेस्क',
            'icon' => 'fa-desktop',
            'color' => '#0891b2',
            'bg_tint' => '#ecfeff',
            'category' => 'desk'
        ],
        'gallery_view' => [
            'title_en' => 'Media Gallery',
            'title_hi' => 'फोटो एवं मीडिया गैलरी',
            'desc' => 'Campus event albums, annual celebrations & photo archives',
            'desc_hi' => 'संस्थान के फोटो एल्बम, सांस्कृतिक कार्यक्रम एवं अभिलेखागार',
            'icon' => 'fa-picture-o',
            'color' => '#9333ea',
            'bg_tint' => '#faf5ff',
            'category' => 'desk'
        ],
        'rules_add' => [
            'title_en' => 'Rules & Regulations',
            'title_hi' => 'नियम एवं आचार संहिता',
            'desc' => 'Institutional rules, code of conduct & discipline policy',
            'desc_hi' => 'संस्थान की आचार संहिता, अनुशासन नीति एवं दिशा-निर्देश',
            'icon' => 'fa-gavel',
            'color' => '#334155',
            'bg_tint' => '#f8fafc',
            'category' => 'admin'
        ],
        'prayer_view' => [
            'title_en' => 'Morning Prayer',
            'title_hi' => 'प्रार्थना एवं सुविचार',
            'desc' => 'Daily morning assembly prayer schedule & thought for the day',
            'desc_hi' => 'दैनिक प्रार्थना सभा, सुविचार एवं प्रतिज्ञा सूची',
            'icon' => 'fa-sun-o',
            'color' => '#ea580c',
            'bg_tint' => '#fff7ed',
            'category' => 'desk'
        ],
        'books_uniform_view' => [
            'title_en' => 'Books & Uniform',
            'title_hi' => 'पुस्तकें एवं वर्दी सूची',
            'desc' => 'Syllabus textbook lists, stationery kits & uniform items',
            'desc_hi' => 'पाठ्यपुस्तक सूची, स्टेशनरी किट एवं वर्दी विवरण',
            'icon' => 'fa-shopping-bag',
            'color' => '#059669',
            'bg_tint' => '#ecfdf5',
            'category' => 'academic'
        ],
        'uniform_add' => [
            'title_en' => 'Uniform Setup',
            'title_hi' => 'वर्दी विनिर्देश',
            'desc' => 'Student dress code guidelines, seasonal specifications & patterns',
            'desc_hi' => 'छात्र पोशाक नियम, मौसमी वर्दी विनिर्देश एवं पैटर्न',
            'icon' => 'fa-user-secret',
            'color' => '#4f46e5',
            'bg_tint' => '#eef2ff',
            'category' => 'desk'
        ],
        'bank/account/index' => [
            'title_en' => 'Bank Accounts Panel',
            'title_hi' => 'बैंक खाता पैनल',
            'desc' => 'Institution bank accounts, ledger books & IFSC routing',
            'desc_hi' => 'संस्थान के बैंक खाते, खाता बही एवं आईएफएससी विवरण',
            'icon' => 'fa-university',
            'color' => '#1e293b',
            'bg_tint' => '#f1f5f9',
            'category' => 'admin'
        ],
        'view_ip_setting' => [
            'title_en' => 'IP Access Control',
            'title_hi' => 'आईपी सुरक्षा सेटिंग्स',
            'desc' => 'Secure IP whitelisting & restricted network login access',
            'desc_hi' => 'सुरक्षित आईपी श्वेतसूची एवं नेटवर्क लॉगिन सुरक्षा',
            'icon' => 'fa-shield',
            'color' => '#dc2626',
            'bg_tint' => '#fef2f2',
            'category' => 'admin'
        ],
        'printFilePanel' => [
            'title_en' => 'Print Files Panel',
            'title_hi' => 'प्रिंट फाइल पैनल',
            'desc' => 'Custom printable document templates, letterheads & headers',
            'desc_hi' => 'प्रिंट योग्य दस्तावेज़ टेम्पलेट, लेटरहेड एवं हेडर प्रारूप',
            'icon' => 'fa-print',
            'color' => '#0891b2',
            'bg_tint' => '#ecfeff',
            'category' => 'admin'
        ],
        'recycle_bin/view' => [
            'title_en' => 'Recycle Bin',
            'title_hi' => 'रीसायकल बिन',
            'desc' => 'Deleted record recovery, audit logs & restoration center',
            'desc_hi' => 'हटाए गए डेटा की पुनर्प्राप्ति एवं रीसायकल बिन',
            'icon' => 'fa-trash-o',
            'color' => '#64748b',
            'bg_tint' => '#f8fafc',
            'category' => 'admin'
        ],
        'busDashboard' => [
            'title_en' => 'Transport / Bus',
            'title_hi' => 'परिवहन / बस',
            'desc' => 'Bus routes, vehicle fleet, driver details & stops',
            'desc_hi' => 'बस मार्ग, वाहन बेड़ा, चालक विवरण एवं स्टॉपेज',
            'icon' => 'fa-bus',
            'color' => '#f59e0b',
            'bg_tint' => '#fffbeb',
            'category' => 'desk'
        ]
    ];
@endphp

<style>
/* ========================================================
   MASTER DASHBOARD - EXCEL GREEN EXECUTIVE THEME (#217346)
   ======================================================== */
:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #f0fdf4;
    --green-excel-border: #a7d0b8;
    --line: #b8c8d5;
    --line-subtle: #cbd5e1;
    --text-main: #0f172a;
}

.master-dash-page {
    background-color: #f4f6f9;
    font-size: 12px;
}

/* Main Dashboard Card Wrapper */
.dash-main-card {
    border: 1px solid var(--line) !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
    background: #ffffff;
    margin-bottom: 15px;
    overflow: hidden;
}

/* Executive Header Bar */
.dash-titlebar {
    min-height: 44px;
    padding: 6px 14px;
    background: var(--green-excel) !important;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.dash-title {
    margin: 0;
    font-size: 14.5px;
    font-weight: 700;
    letter-spacing: 0.2px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffffff !important;
}

.dash-title i {
    color: #ffc107;
}

.dash-title small {
    font-size: 11.5px;
    font-weight: 400;
    color: #e2f0d9;
    margin-left: 6px;
}

.dash-title-tools {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.btn-header-action {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 4px 10px !important;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    line-height: 1.35;
}
.btn-header-action:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}
.btn-header-action.btn-header-add {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    color: #166534 !important;
    font-weight: 700 !important;
}
.btn-header-action.btn-header-add:hover {
    background: #dcfce7 !important;
    border-color: #4ade80 !important;
    color: #14532d !important;
}

/* Fast Search in Header */
.dash-quick-filter {
    position: relative;
    display: flex;
    align-items: center;
}

.dash-quick-filter i {
    position: absolute;
    left: 8px;
    color: #e2f0d9;
    font-size: 11px;
    pointer-events: none;
    transition: color 0.15s ease;
}

.dash-quick-filter input {
    height: 27px;
    width: 170px;
    padding: 2px 8px 2px 24px;
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 3px;
    font-size: 11.5px;
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    transition: all 0.2s ease;
}

.dash-quick-filter input::placeholder {
    color: #e2f0d9;
    opacity: 0.85;
}

.dash-quick-filter input:focus {
    width: 210px;
    background: #ffffff;
    color: #0f172a;
    border-color: #ffffff;
    outline: none;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.35);
}

.dash-quick-filter:focus-within i {
    color: #64748b;
}

/* Card Buttons Body & Grid */
.dash-body {
    padding: 12px 14px;
}

.dash-card-btn {
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1px solid var(--line-subtle);
    border-radius: 4px;
    height: 48px;
    padding: 4px 10px 4px 0;
    position: relative;
    overflow: hidden;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.dash-card-btn:hover {
    background: #f0fdf4;
    border-color: var(--green-excel);
    box-shadow: 0 2px 7px rgba(33, 115, 70, 0.15);
    transform: translateY(-1px);
}

.dash-btn-stripe {
    width: 3.5px;
    height: 100%;
    position: absolute;
    left: 0;
    top: 0;
    transition: width 0.15s ease;
}

.dash-card-btn:hover .dash-btn-stripe {
    width: 5px;
}

.dash-btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 4px;
    margin-left: 9px;
    margin-right: 9px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    border: 1px solid rgba(0, 0, 0, 0.04);
}

.dash-btn-body {
    flex-grow: 1;
    min-width: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.dash-btn-title {
    font-size: 12.5px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.15s ease;
}

.dash-card-btn:hover .dash-btn-title {
    color: var(--green-excel);
}

.dash-btn-sub {
    font-size: 10.5px;
    color: #64748b;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
}

.dash-btn-arrow {
    color: #94a3b8;
    font-size: 14px;
    margin-left: 6px;
    flex-shrink: 0;
    transition: transform 0.15s ease, color 0.15s ease;
}

.dash-card-btn:hover .dash-btn-arrow {
    color: var(--green-excel);
    transform: translateX(3px);
}

/* Category separator / micro header */
.dash-section-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 4px 8px 4px;
    font-size: 11.5px;
    color: #64748b;
    font-weight: 600;
    flex-wrap: wrap;
    gap: 8px;
}

/* Category Filter Pills */
.dash-category-pills {
    display: inline-flex;
    gap: 4px;
    align-items: center;
    flex-wrap: wrap;
}

.dash-pill-btn {
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    border-radius: 12px;
    padding: 2px 9px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.35;
    cursor: pointer;
    transition: all 0.15s ease;
    outline: none !important;
}

.dash-pill-btn:hover {
    border-color: var(--green-excel);
    color: var(--green-excel);
    background: #f0fdf4;
}

.dash-pill-btn.active {
    background: var(--green-excel);
    border-color: var(--green-excel);
    color: #ffffff;
    box-shadow: 0 1px 3px rgba(33, 115, 70, 0.3);
}
</style>

<div class="content-wrapper master-dash-page">
    <section class="content pt-2">
        <div class="container-fluid px-2">

            <!-- Main Unified Dashboard Card -->
            <div class="dash-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-sitemap"></i>
                        <span>{{ $isHindi ? 'मास्टर प्रबंधन' : __('master.Master Management') }}</span>
                        <small>{{ $isHindi ? 'मुख्य शैक्षणिक विन्यास एवं संस्थान मास्टर रिकॉर्ड' : 'Core Academic Configuration & Institution Master Records' }}</small>
                    </h1>
                    
                    <div class="dash-title-tools">
                        <div class="dash-quick-filter mr-1">
                            <i class="fa fa-search"></i>
                            <input type="search" id="dashModuleSearch" name="search_master_module_quick" placeholder="{{ $isHindi ? 'मॉड्यूल खोजें...' : 'Quick find module...' }}" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-1p-ignore readonly onfocus="this.removeAttribute('readonly');" onclick="this.removeAttribute('readonly');">
                        </div>
                        <a href="{{ url('course_add') }}" class="btn-header-action btn-header-add" title="{{ $isHindi ? 'पाठ्यक्रम एवं डिग्री' : 'Courses & Programs' }}">
                            <i class="fa fa-graduation-cap text-success mr-1"></i> {{ $isHindi ? 'पाठ्यक्रम' : 'Courses' }}
                        </a>
                        <a href="{{ url('add_class') }}" class="btn-header-action" title="{{ $isHindi ? 'कक्षाएं एवं अनुभाग' : 'Classes & Sections' }}">
                            <i class="fa fa-university text-primary mr-1"></i> {{ $isHindi ? 'कक्षाएं' : 'Classes' }}
                        </a>
                        <a href="{{ url('add_weekend') }}" class="btn-header-action" title="{{ $isHindi ? 'शैक्षणिक कैलेंडर' : 'Academic Calendar' }}">
                            <i class="fa fa-calendar-check-o text-warning mr-1"></i> {{ $isHindi ? 'कैलेंडर' : 'Calendar' }}
                        </a>
                    </div>
                </div>

                <!-- 2. Compact Card Buttons Body & Grid -->
                <div class="dash-body">
                    
                    <div class="dash-section-meta">
                        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                            <span><i class="fa fa-th-large mr-1 text-success"></i> {{ $isHindi ? 'उपलब्ध मॉड्यूल' : 'Available Modules' }} ({{ count($sidebarSubList) }})</span>
                            <div class="dash-category-pills">
                                <button type="button" class="dash-pill-btn active" data-cat="all">{{ $isHindi ? 'सभी' : 'All' }}</button>
                                <button type="button" class="dash-pill-btn" data-cat="academic"><i class="fa fa-graduation-cap mr-1"></i> {{ $isHindi ? 'शैक्षणिक' : 'Academic' }}</button>
                                <button type="button" class="dash-pill-btn" data-cat="communication"><i class="fa fa-comments mr-1"></i> {{ $isHindi ? 'संचार' : 'Communication' }}</button>
                                <button type="button" class="dash-pill-btn" data-cat="admin"><i class="fa fa-cogs mr-1"></i> {{ $isHindi ? 'प्रशासन' : 'Admin & Security' }}</button>
                                <button type="button" class="dash-pill-btn" data-cat="desk"><i class="fa fa-desktop mr-1"></i> {{ $isHindi ? 'कैंपस डेस्क' : 'Campus Desk' }}</button>
                            </div>
                        </div>
                        <span id="filterCountNotice" style="display: none; color: #217346; font-weight: 700;"></span>
                    </div>

                    <div class="row mx-n1" id="modulesGrid">
                        @if(!empty($sidebarSubList) && count($sidebarSubList) > 0)
                            @foreach($sidebarSubList as $sub)
                                @php
                                    $url = trim($sub['url'] ?? '');
                                    $rawName = trim(preg_replace('/\s+/', ' ', $sub['name'] ?? ''));
                                    $meta = $metaMap[$url] ?? [
                                        'title_en' => $rawName,
                                        'title_hi' => !empty($sub['hindi_name']) ? $sub['hindi_name'] : $rawName,
                                        'icon' => !empty($sub['ican']) ? trim(str_replace('fa ', '', $sub['ican'])) : 'fa-cubes',
                                        'desc' => 'Configure & manage ' . $rawName . ' master records',
                                        'desc_hi' => $rawName . ' मास्टर रिकॉर्ड प्रबंधित करें',
                                        'color' => '#217346',
                                        'bg_tint' => '#f0fdf4',
                                        'category' => 'admin'
                                    ];

                                    $displayTitle = $isHindi ? (!empty($meta['title_hi']) ? $meta['title_hi'] : $rawName) : (!empty($meta['title_en']) ? $meta['title_en'] : $rawName);
                                    $displayDesc = $isHindi ? (!empty($meta['desc_hi']) ? $meta['desc_hi'] : $meta['desc']) : $meta['desc'];
                                    $category = $meta['category'] ?? 'all';
                                    $iconClass = strpos($meta['icon'], 'fa-') !== false ? $meta['icon'] : 'fa-' . $meta['icon'];
                                @endphp
                                <div class="col-xl-3 col-lg-4 col-md-6 col-12 px-1 mb-2 module-card-item" 
                                     data-category="{{ $category }}"
                                     data-keywords="{{ strtolower($displayTitle . ' ' . $rawName . ' ' . $displayDesc . ' ' . $url . ' ' . $category) }}">
                                    <a href="{{ url($url) }}" class="dash-card-btn" title="{{ $displayTitle }}">
                                        <div class="dash-btn-stripe" style="background: {{ $meta['color'] }};"></div>
                                        <div class="dash-btn-icon" style="background: {{ $meta['bg_tint'] }}; color: {{ $meta['color'] }};">
                                            <i class="fa {{ $iconClass }}"></i>
                                        </div>
                                        <div class="dash-btn-body">
                                            <div class="dash-btn-title">{{ $displayTitle }}</div>
                                            <div class="dash-btn-sub">{{ $displayDesc }}</div>
                                        </div>
                                        <div class="dash-btn-arrow">
                                            <i class="fa fa-angle-right"></i>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center py-4 text-muted">
                                <i class="fa fa-lock fa-2x mb-2 text-secondary"></i>
                                <p class="mb-0 font-weight-bold" style="font-size: 13px;">No master management permissions assigned to your role.</p>
                            </div>
                        @endif
                    </div>

                    <!-- No Search Results Found Row -->
                    <div id="noResultsNotice" class="text-center py-4 text-muted" style="display: none;">
                        <i class="fa fa-search fa-2x mb-2 text-secondary"></i>
                        <p class="mb-0 font-weight-bold" style="font-size: 13px;">No module matched your search.</p>
                    </div>

                </div>

            </div>

        </div>
    </section>
</div>

<script>
$(function(){
    var activeCategory = 'all';

    function filterModules() {
        var term = $.trim($('#dashModuleSearch').val()).toLowerCase();
        var items = $('.module-card-item');
        var matched = 0;

        items.each(function(){
            var item = $(this);
            var itemCategory = item.data('category') || '';
            var keywords = item.data('keywords') || '';

            var matchesCategory = (activeCategory === 'all' || itemCategory === activeCategory);
            var matchesSearch = (term === '' || keywords.indexOf(term) !== -1);

            if (matchesCategory && matchesSearch) {
                item.show();
                matched++;
            } else {
                item.hide();
            }
        });

        if (matched === 0) {
            $('#noResultsNotice').show();
            $('#filterCountNotice').hide();
        } else {
            $('#noResultsNotice').hide();
            if (term !== '' || activeCategory !== 'all') {
                $('#filterCountNotice').text('Showing ' + matched + ' of ' + items.length).show();
            } else {
                $('#filterCountNotice').hide();
            }
        }
    }

    // Instant module search / filter
    $('#dashModuleSearch').on('input', function(){
        filterModules();
    });

    // Category pills filter
    $('.dash-pill-btn').on('click', function(){
        $('.dash-pill-btn').removeClass('active');
        $(this).addClass('active');
        activeCategory = $(this).data('cat') || 'all';
        filterModules();
    });
});
</script>

@endsection
