@extends('layout.app')
@section('content')

@php
    $sidebarSubList = Helper::SidebarSubPerm(3) ?? [];
    $isHindi = Session::get('locale') == 'hi';

    // Module metadata mapping with Excel Green & Executive accents
    $metaMap = [
        'enquiryAdd' => [
            'icon' => 'fa-pencil-square-o',
            'desc' => 'New student enquiry entry & walk-in form',
            'color' => '#ea580c',
            'bg_tint' => '#fff7ed'
        ],
        'enquiryView' => [
            'icon' => 'fa-list-ul',
            'desc' => 'View leads, follow-ups & filter inquiries',
            'color' => '#0284c7',
            'bg_tint' => '#f0f9ff'
        ],
        'admissionAdd' => [
            'icon' => 'fa-user-plus',
            'desc' => 'Enroll new student & register admission',
            'color' => '#217346',
            'bg_tint' => '#f0fdf4'
        ],
        'admissionView' => [
            'icon' => 'fa-users',
            'desc' => 'Student master directory & document verification',
            'color' => '#166534',
            'bg_tint' => '#f0fdf4'
        ],
        'studentsAttendanceAdd' => [
            'icon' => 'fa-calendar-check-o',
            'desc' => 'Mark daily / session attendance register',
            'color' => '#d97706',
            'bg_tint' => '#fffbeb'
        ],
        'studentsAttendanceView' => [
            'icon' => 'fa-bar-chart',
            'desc' => 'Monthly attendance sheets & absenteeism records',
            'color' => '#217346',
            'bg_tint' => '#f0fdf4'
        ],
        'student/promote_add' => [
            'icon' => 'fa-level-up',
            'desc' => 'Migrate cohort to next semester & fee carry',
            'color' => '#4f46e5',
            'bg_tint' => '#eef2ff'
        ],
        'login_credential_reports' => [
            'icon' => 'fa-key',
            'desc' => 'Student app accounts, usernames & passwords',
            'color' => '#0d9488',
            'bg_tint' => '#f0fdfa'
        ],
        'customIdCard' => [
            'icon' => 'fa-id-card-o',
            'desc' => 'Bulk generate & print student identity cards',
            'color' => '#7c3aed',
            'bg_tint' => '#faf5ff'
        ],
        'student/id/index' => [
            'icon' => 'fa-id-badge',
            'desc' => 'Standard ID card templates & exports',
            'color' => '#0284c7',
            'bg_tint' => '#f0f9ff'
        ],
        'student_performance' => [
            'icon' => 'fa-trophy',
            'desc' => 'Exam evaluations, marks & performance reports',
            'color' => '#d97706',
            'bg_tint' => '#fffbeb'
        ],
        'stream_update' => [
            'icon' => 'fa-exchange',
            'desc' => 'Batch, stream & subject elective transfer',
            'color' => '#475569',
            'bg_tint' => '#f8fafc'
        ],
        'category_wise_report' => [
            'icon' => 'fa-pie-chart',
            'desc' => 'Quota, reservation & demographic analysis',
            'color' => '#166534',
            'bg_tint' => '#f0fdf4'
        ],
        'agents' => [
            'icon' => 'fa-handshake-o',
            'desc' => 'Agent directory, commissions & referrers list',
            'color' => '#217346',
            'bg_tint' => '#f0fdf4'
        ]
    ];
@endphp

<style>
/* ========================================================
   STUDENTS DASHBOARD - EXCEL GREEN EXECUTIVE THEME (#217346)
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

.student-dash-page {
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
}
</style>

<div class="content-wrapper student-dash-page">
    <section class="content pt-2">
        <div class="container-fluid px-2">

            
            <!-- Main Unified Dashboard Card -->
            <div class="dash-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-graduation-cap"></i>
                        <span>{{ __('student.Students Management') }}</span>
                        <small>Academic Registry &amp; Student Operations Dashboard</small>
                    </h1>
                    
                    <div class="dash-title-tools">
                        <div class="dash-quick-filter mr-1">
                            <i class="fa fa-search"></i>
                            <input type="search" id="dashModuleSearch" name="search_student_module_quick" placeholder="Quick find module..." autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-1p-ignore readonly onfocus="this.removeAttribute('readonly');" onclick="this.removeAttribute('readonly');">
                        </div>
                        <a href="{{ url('admissionAdd') }}" class="btn-header-action btn-header-add" title="New Admission">
                            <i class="fa fa-plus text-success mr-1"></i> New Admission
                        </a>
                        <a href="{{ url('student/promote_add') }}" class="btn-header-action" title="Student Promote">
                            <i class="fa fa-level-up text-warning mr-1"></i> Student Promote
                        </a>
                        <a href="{{ url('fees/ledger') }}" class="btn-header-action" title="Fees Ledger">
                            <i class="fa fa-table text-primary mr-1"></i> Fees Ledger
                        </a>
                    </div>
                </div>

                <!-- 2. Compact Card Buttons Grid -->
                <div class="dash-body">
                    
                    <div class="dash-section-meta">
                        <span><i class="fa fa-th-large mr-1 text-success"></i> Available Modules ({{ count($sidebarSubList) }})</span>
                        <span id="filterCountNotice" style="display: none; color: #217346; font-weight: 700;"></span>
                    </div>

                    <div class="row mx-n1" id="modulesGrid">
                        @if(!empty($sidebarSubList))
                            @foreach($sidebarSubList as $sub)
                                @php
                                    $url = $sub['url'] ?? '';
                                    $name = $isHindi ? ($sub['hindi_name'] ?? $sub['name']) : ($sub['name'] ?? '');
                                    $meta = $metaMap[$url] ?? [
                                        'icon' => !empty($sub['ican']) ? $sub['ican'] : 'fa-folder-open-o',
                                        'desc' => 'Manage & view ' . $name,
                                        'color' => '#217346',
                                        'bg_tint' => '#f0fdf4'
                                    ];
                                @endphp
                                <div class="col-xl-3 col-lg-4 col-md-6 col-12 px-1 mb-2 module-card-item" data-keywords="{{ strtolower($name . ' ' . $meta['desc'] . ' ' . $url) }}">
                                    <a href="{{ url($url) }}" class="dash-card-btn" title="{{ $name }}">
                                        <div class="dash-btn-stripe" style="background: {{ $meta['color'] }};"></div>
                                        <div class="dash-btn-icon" style="background: {{ $meta['bg_tint'] }}; color: {{ $meta['color'] }};">
                                            <i class="fa {{ $meta['icon'] }}"></i>
                                        </div>
                                        <div class="dash-btn-body">
                                            <div class="dash-btn-title">{{ $name }}</div>
                                            <div class="dash-btn-sub">{{ $meta['desc'] }}</div>
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
                                <p class="mb-0 font-weight-bold" style="font-size: 13px;">No student management permissions assigned to your role.</p>
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
    // Instant module search / filter
    $('#dashModuleSearch').on('input', function(){
        var term = $.trim($(this).val()).toLowerCase();
        var items = $('.module-card-item');
        var matched = 0;

        if (term === '') {
            items.show();
            $('#filterCountNotice').hide();
            $('#noResultsNotice').hide();
            return;
        }

        items.each(function(){
            var keywords = $(this).data('keywords') || '';
            if (keywords.indexOf(term) !== -1) {
                $(this).show();
                matched++;
            } else {
                $(this).hide();
            }
        });

        if (matched === 0) {
            $('#noResultsNotice').show();
            $('#filterCountNotice').hide();
        } else {
            $('#noResultsNotice').hide();
            $('#filterCountNotice').text('Showing ' + matched + ' of ' + items.length).show();
        }
    });
});
</script>

@endsection