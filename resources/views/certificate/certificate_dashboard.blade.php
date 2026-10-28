@extends('layout.app')
@section('content')

@php
    $sidebarSubList = Helper::SidebarSubPerm(5) ?? [];
    $isHindi = Session::get('locale') == 'hi';

    // Real-time certificate count metrics
    $c_certificate_count = Helper::getCount('c_certificates_form', 'id', 'count') ?? 0;
    $event_count = Helper::getCount('evente_certificates', 'id', 'count') ?? 0;
    $sports_count = Helper::getCount('sports_certificates', 'id', 'count') ?? 0;
    $tc_count = Helper::getCount('tc_certificates', 'id', 'count') ?? 0;

    // Certificate module metadata mapping with Excel Green & Executive accents
    $metaMap = [
        'cc/form/add' => [
            'title_en' => 'Character Certificate (CC) Entry',
            'title_hi' => 'नया चरित्र प्रमाणपत्र (CC)',
            'desc' => 'Generate & issue student character conduct certificate',
            'desc_hi' => 'विद्यार्थी आचरण व चरित्र प्रमाणपत्र जारी करें',
            'icon' => 'fa-certificate',
            'color' => '#059669',
            'bg_tint' => '#ecfdf5',
            'category' => 'cc'
        ],
        'cc/form/index' => [
            'title_en' => 'Character Certificates Directory',
            'title_hi' => 'चरित्र प्रमाणपत्र सूची',
            'desc' => 'Search, view & print issued character certificates',
            'desc_hi' => 'जारी चरित्र प्रमाणपत्र देखें व प्रिंट करें',
            'icon' => 'fa-list-alt',
            'color' => '#0284c7',
            'bg_tint' => '#f0f9ff',
            'category' => 'cc'
        ],
        'tc/certificate/add' => [
            'title_en' => 'Transfer Certificate (TC) Entry',
            'title_hi' => 'स्थानांतरण प्रमाणपत्र (TC) प्रविष्टि',
            'desc' => 'Issue college living & transfer certificate for students',
            'desc_hi' => 'विद्यार्थी स्थानांतरण एवं टीसी प्रमाणपत्र जारी करें',
            'icon' => 'fa-file-text-o',
            'color' => '#d97706',
            'bg_tint' => '#fffbeb',
            'category' => 'tc'
        ],
        'tc/certificate/index' => [
            'title_en' => 'Transfer Certificates Directory',
            'title_hi' => 'स्थानांतरण प्रमाणपत्र सूची',
            'desc' => 'Master directory of issued TC certificates & records',
            'desc_hi' => 'जारी किए गए टीसी प्रमाणपत्रों की मास्टर सूची',
            'icon' => 'fa-th-list',
            'color' => '#b91c1c',
            'bg_tint' => '#fef2f2',
            'category' => 'tc'
        ],
        'evente/certificate/add' => [
            'title_en' => 'Event Certificate Entry',
            'title_hi' => 'इवेंट प्रमाणपत्र प्रविष्टि',
            'desc' => 'Issue merit & participation certificates for cultural events',
            'desc_hi' => 'सांस्कृतिक कार्यक्रमों हेतु सहभागिता प्रमाणपत्र',
            'icon' => 'fa-trophy',
            'color' => '#7c3aed',
            'bg_tint' => '#faf5ff',
            'category' => 'events'
        ],
        'evente/certificate/index' => [
            'title_en' => 'Event Certificates Directory',
            'title_hi' => 'इवेंट प्रमाणपत्र सूची',
            'desc' => 'View & print historical event participation records',
            'desc_hi' => 'आयोजन सहभागिता प्रमाणपत्र रिकॉर्ड सूची',
            'icon' => 'fa-flag-checkered',
            'color' => '#9333ea',
            'bg_tint' => '#faf5ff',
            'category' => 'events'
        ],
        'sport/certificate/add' => [
            'title_en' => 'Sports Certificate Entry',
            'title_hi' => 'खेलकूद प्रमाणपत्र प्रविष्टि',
            'desc' => 'Issue athletic achievement & tournament certificates',
            'desc_hi' => 'खेलकूद प्रतियोगिता उपलब्धि प्रमाणपत्र जारी करें',
            'icon' => 'fa-dribbble',
            'color' => '#ea580c',
            'bg_tint' => '#fff7ed',
            'category' => 'events'
        ],
        'sport/certificate/index' => [
            'title_en' => 'Sports Certificates Directory',
            'title_hi' => 'खेलकूद प्रमाणपत्र सूची',
            'desc' => 'Search & print issued sports tournament certificates',
            'desc_hi' => 'खेलकूद प्रमाणपत्रों की खोज एवं प्रिंट सूची',
            'icon' => 'fa-futbol-o',
            'color' => '#0891b2',
            'bg_tint' => '#ecfeff',
            'category' => 'events'
        ],
        'certificate_editor' => [
            'title_en' => 'Certificate Template Editor',
            'title_hi' => 'प्रमाणपत्र टेम्पलेट संपादक',
            'desc' => 'Design & customize certificate layout templates',
            'desc_hi' => 'प्रमाणपत्र लेआउट एवं टेम्पलेट डिज़ाइन करें',
            'icon' => 'fa-edit',
            'color' => '#475569',
            'bg_tint' => '#f8fafc',
            'category' => 'tools'
        ]
    ];
@endphp

<style>
/* ========================================================
   CERTIFICATE DASHBOARD - EXCEL GREEN EXECUTIVE THEME (#217346)
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

.certificate-dash-page {
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
    width: 180px;
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
    width: 220px;
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

.cert-stat-card {
    background: #ffffff;
    border: 1px solid var(--line-subtle);
    border-radius: 4px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.cert-stat-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.06);
}
</style>

<div class="content-wrapper certificate-dash-page">
    <section class="content pt-2">
        <div class="container-fluid px-2">

            <!-- Main Unified Dashboard Card -->
            <div class="dash-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-certificate"></i>
                        <span>{{ $isHindi ? 'प्रमाणपत्र प्रबंधन' : __('certificate.Certificate Management') }}</span>
                        <small>{{ $isHindi ? 'चरित्र, स्थानांतरण, खेलकूद एवं इवेंट प्रमाणपत्र प्रबंधन केंद्र' : 'Student Character, Transfer, Sports & Event Certification Hub' }}</small>
                    </h1>
                    
                    <div class="dash-title-tools">
                        <div class="dash-quick-filter mr-1">
                            <i class="fa fa-search"></i>
                            <input type="search" id="dashModuleSearch" name="search_cert_module_quick" placeholder="{{ $isHindi ? 'प्रमाणपत्र खोजें...' : 'Quick find module...' }}" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-1p-ignore readonly onfocus="this.removeAttribute('readonly');" onclick="this.removeAttribute('readonly');">
                        </div>
                        <a href="{{ url('cc/form/add') }}" class="btn-header-action btn-header-add" title="{{ $isHindi ? 'नया चरित्र प्रमाणपत्र' : 'New Character Certificate' }}">
                            <i class="fa fa-plus text-success mr-1"></i> {{ $isHindi ? 'नया CC Form' : 'New CC Form' }}
                        </a>
                        <a href="{{ url('tc/certificate/add') }}" class="btn-header-action" title="{{ $isHindi ? 'नया टीसी प्रमाणपत्र' : 'New Transfer Certificate' }}">
                            <i class="fa fa-file-text-o text-warning mr-1"></i> {{ $isHindi ? 'नया TC Form' : 'New TC Form' }}
                        </a>
                        <a href="{{ url('evente/certificate/add') }}" class="btn-header-action" title="{{ $isHindi ? 'इवेंट प्रमाणपत्र' : 'Event Certificate' }}">
                            <i class="fa fa-trophy text-info mr-1"></i> {{ $isHindi ? 'इवेंट प्रमाणपत्र' : 'Event Cert' }}
                        </a>
                        <a href="{{ url('sport/certificate/add') }}" class="btn-header-action" title="{{ $isHindi ? 'खेलकूद प्रमाणपत्र' : 'Sports Certificate' }}">
                            <i class="fa fa-dribbble text-primary mr-1"></i> {{ $isHindi ? 'खेलकूद प्रमाणपत्र' : 'Sports Cert' }}
                        </a>
                    </div>
                </div>

                <!-- 2. Body Grid & Available Modules -->
                <div class="dash-body">
                    
                    <!-- Category Pills & Count Header -->
                    <div class="dash-section-meta">
                        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                            <span><i class="fa fa-th-large mr-1 text-success"></i> {{ $isHindi ? 'उपलब्ध प्रमाणपत्र मॉड्यूल' : 'Available Certificate Modules' }} ({{ count($sidebarSubList) }})</span>
                            <div class="dash-category-pills">
                                <button type="button" class="dash-pill-btn active" data-cat="all">{{ $isHindi ? 'सभी' : 'All' }}</button>
                                <button type="button" class="dash-pill-btn" data-cat="cc"><i class="fa fa-certificate mr-1"></i> {{ $isHindi ? 'चरित्र (CC)' : 'Character (CC)' }}</button>
                                <button type="button" class="dash-pill-btn" data-cat="tc"><i class="fa fa-file-text-o mr-1"></i> {{ $isHindi ? 'टी.सी. (TC)' : 'Transfer (TC)' }}</button>
                                <button type="button" class="dash-pill-btn" data-cat="events"><i class="fa fa-trophy mr-1"></i> {{ $isHindi ? 'इवेंट व खेलकूद' : 'Events & Sports' }}</button>
                                <button type="button" class="dash-pill-btn" data-cat="tools"><i class="fa fa-cogs mr-1"></i> {{ $isHindi ? 'टूल' : 'Tools' }}</button>
                            </div>
                        </div>
                        <span id="filterCountNotice" style="display: none; color: #217346; font-weight: 700;"></span>
                    </div>

                    <!-- Module Cards Grid -->
                    <div class="row mx-n1" id="modulesGrid">
                        @if(!empty($sidebarSubList) && count($sidebarSubList) > 0)
                            @foreach($sidebarSubList as $sub)
                                @php
                                    $url = trim($sub['url'] ?? '');
                                    $rawName = trim(preg_replace('/\s+/', ' ', $sub['name'] ?? ''));
                                    $meta = $metaMap[$url] ?? [
                                        'title_en' => $rawName,
                                        'title_hi' => !empty($sub['hindi_name']) ? $sub['hindi_name'] : $rawName,
                                        'icon' => !empty($sub['ican']) ? trim(str_replace('fa ', '', $sub['ican'])) : 'fa-certificate',
                                        'desc' => 'Manage & view ' . $rawName . ' records',
                                        'desc_hi' => $rawName . ' रिकॉर्ड प्रबंधित करें',
                                        'color' => '#217346',
                                        'bg_tint' => '#f0fdf4',
                                        'category' => 'cc'
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
                                <p class="mb-0 font-weight-bold" style="font-size: 13px;">No certificate management permissions assigned to your role.</p>
                            </div>
                        @endif
                    </div>

                    <!-- No Search Results Found Row -->
                    <div id="noResultsNotice" class="text-center py-4 text-muted" style="display: none;">
                        <i class="fa fa-search fa-2x mb-2 text-secondary"></i>
                        <p class="mb-0 font-weight-bold" style="font-size: 13px;">No certificate module matched your search.</p>
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