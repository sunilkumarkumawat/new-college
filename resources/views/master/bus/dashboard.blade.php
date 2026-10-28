@extends('layout.app')
@section('content')

@php
    $sidebarSubList = Helper::SidebarSubPerm(20) ?? [];
    $isHindi = Session::get('locale') == 'hi';

    // Bus module metadata mapping with Excel Green & Executive accents
    $metaMap = [
        'busAdd' => [
            'title_en' => 'Add Bus / Vehicle Entry',
            'title_hi' => 'नई बस प्रविष्टि',
            'desc' => 'Register new vehicle, registration & owner details',
            'desc_hi' => 'नई बस का पंजीकरण व विवरण दर्ज करें',
            'icon' => 'fa-bus',
            'color' => '#059669',
            'bg_tint' => '#ecfdf5',
            'category' => 'fleet'
        ],
        'busView' => [
            'title_en' => 'Bus Fleet Directory',
            'title_hi' => 'बस वाहन सूची',
            'desc' => 'Master directory of registered buses & vehicles',
            'desc_hi' => 'पंजीकृत बसों व वाहनों की मास्टर सूची',
            'icon' => 'fa-list-alt',
            'color' => '#0284c7',
            'bg_tint' => '#f0f9ff',
            'category' => 'fleet'
        ],
        'busRouteAdd' => [
            'title_en' => 'Bus Route & Stops Entry',
            'title_hi' => 'बस रूट व स्टॉप प्रविष्टि',
            'desc' => 'Create new transportation routes & pickup points',
            'desc_hi' => 'नए बस रूट व स्टॉपेज पॉइंट बनाएं',
            'icon' => 'fa-road',
            'color' => '#d97706',
            'bg_tint' => '#fffbeb',
            'category' => 'routes'
        ],
        'assignBusRoute' => [
            'title_en' => 'Assign Bus to Route',
            'title_hi' => 'बस रूट आवंटन',
            'desc' => 'Link vehicles with specific travel routes & drivers',
            'desc_hi' => 'वाहनों को बस रूट एवं चालकों के साथ जोड़ें',
            'icon' => 'fa-object-group',
            'color' => '#7c3aed',
            'bg_tint' => '#faf5ff',
            'category' => 'routes'
        ],
        'assignBus' => [
            'title_en' => 'Assign Student Transport',
            'title_hi' => 'विद्यार्थी बस आवंटन',
            'desc' => 'Assign bus route & pickup points to students',
            'desc_hi' => 'विद्यार्थियों को बस सेवा व स्टॉप आवंटित करें',
            'icon' => 'fa-users',
            'color' => '#ea580c',
            'bg_tint' => '#fff7ed',
            'category' => 'assignment'
        ],
        'studentBusView' => [
            'title_en' => 'Student Transport Directory',
            'title_hi' => 'विद्यार्थी बस आवंटन सूची',
            'desc' => 'Directory of students assigned to bus routes',
            'desc_hi' => 'बस आवंटित विद्यार्थियों की सूची व विवरण',
            'icon' => 'fa-th-list',
            'color' => '#0891b2',
            'bg_tint' => '#ecfeff',
            'category' => 'assignment'
        ]
    ];
@endphp

<style>
/* ========================================================
   BUS DASHBOARD - EXCEL GREEN EXECUTIVE THEME (#217346)
   (Matching 1:1 with https://girraj.test/certificate_dashboard layout)
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

.bus-dash-page {
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
</style>

<div class="content-wrapper bus-dash-page">
    <section class="content pt-2">
        <div class="container-fluid px-2">

            <!-- Main Unified Dashboard Card -->
            <div class="dash-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-bus"></i>
                        <span>{{ $isHindi ? 'बस व परिवहन प्रबंधन' : __('bus.Bus Management') }}</span>
                        <small>{{ $isHindi ? 'बस बेड़ा, रूट, स्टॉपेज व विद्यार्थी बस सेवा प्रबंधन केंद्र' : 'Bus fleet, routes, assignment & student transport tracking hub' }}</small>
                    </h1>
                    
                    <div class="dash-title-tools">
                        <div class="dash-quick-filter mr-1">
                            <i class="fa fa-search"></i>
                            <input type="search" id="dashModuleSearch" name="search_bus_module_quick" placeholder="{{ $isHindi ? 'बस मॉड्यूल खोजें...' : 'Quick find module...' }}" autocomplete="off">
                        </div>
                        <a href="{{ url('busAdd') }}" class="btn-header-action btn-header-add" title="{{ $isHindi ? 'नई बस प्रविष्टि' : 'Add Bus' }}">
                            <i class="fa fa-plus text-success mr-1"></i> {{ $isHindi ? 'नई बस' : 'Add Bus' }}
                        </a>
                        <a href="{{ url('busRouteAdd') }}" class="btn-header-action" title="{{ $isHindi ? 'नया बस रूट' : 'Add Route' }}">
                            <i class="fa fa-road text-warning mr-1"></i> {{ $isHindi ? 'नया रूट' : 'Add Route' }}
                        </a>
                        <a href="{{ url('assignBusRoute') }}" class="btn-header-action" title="{{ $isHindi ? 'बस रूट आवंटन' : 'Assign Route' }}">
                            <i class="fa fa-object-group text-info mr-1"></i> {{ $isHindi ? 'रूट आवंटन' : 'Assign Route' }}
                        </a>
                        <a href="{{ url('master_dashboard') }}" class="btn-header-action" title="Back to Master Dashboard">
                            <i class="fa fa-arrow-left text-secondary mr-1"></i> Back
                        </a>
                    </div>
                </div>

                <!-- 2. Body Grid & Available Modules -->
                <div class="dash-body">
                    
                    <!-- Category Pills & Count Header -->
                    <div class="dash-section-meta">
                        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                            <span><i class="fa fa-th-large mr-1 text-success"></i> {{ $isHindi ? 'उपलब्ध बस एवं परिवहन मॉड्यूल' : 'Available Transport Modules' }} ({{ count($sidebarSubList) }})</span>
                            <div class="dash-category-pills">
                                <button type="button" class="dash-pill-btn active" data-cat="all">{{ $isHindi ? 'सभी' : 'All' }}</button>
                                <button type="button" class="dash-pill-btn" data-cat="fleet"><i class="fa fa-bus mr-1"></i> {{ $isHindi ? 'बस बेड़ा' : 'Bus Fleet' }}</button>
                                <button type="button" class="dash-pill-btn" data-cat="routes"><i class="fa fa-road mr-1"></i> {{ $isHindi ? 'रूट व स्टॉप' : 'Routes & Stops' }}</button>
                                <button type="button" class="dash-pill-btn" data-cat="assignment"><i class="fa fa-users mr-1"></i> {{ $isHindi ? 'छात्र आवंटन' : 'Student Assignment' }}</button>
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
                                        'icon' => !empty($sub['ican']) ? trim(str_replace('fa ', '', $sub['ican'])) : 'fa-bus',
                                        'desc' => 'Manage ' . $rawName . ' records',
                                        'desc_hi' => $rawName . ' रिकॉर्ड प्रबंधित करें',
                                        'color' => '#217346',
                                        'bg_tint' => '#f0fdf4',
                                        'category' => 'fleet'
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
                                <p class="mb-0 font-weight-bold" style="font-size: 13px;">No bus management permissions assigned to your role.</p>
                            </div>
                        @endif
                    </div>

                    <!-- No Search Results Found Row -->
                    <div id="noResultsNotice" class="text-center py-4 text-muted" style="display: none;">
                        <i class="fa fa-search fa-2x mb-2 text-secondary"></i>
                        <p class="mb-0 font-weight-bold" style="font-size: 13px;">No bus module matched your search.</p>
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