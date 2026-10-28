@extends('layout.app')
@section('content')

@php
    $sidebarSubList = Helper::SidebarSubPerm(11) ?? [];
    $isHindi = Session::get('locale') == 'hi';
    $getPermission = Helper::getPermission();
    $getCounters = Helper::getCounters();

    // Module metadata mapping with Executive theme accents
    $metaMap = [
        'Fees/add' => [
            'icon' => 'fa-money',
            'desc' => 'Collect student fees, installments & print receipts',
            'color' => '#16a34a',
            'bg_tint' => '#f0fdf4'
        ],
        'fees_assign' => [
            'icon' => 'fa-users',
            'desc' => 'Assign fee structures, groups & discounts to students',
            'color' => '#2563eb',
            'bg_tint' => '#eff6ff'
        ],
        'feesGroup' => [
            'icon' => 'fa-th-list',
            'desc' => 'Configure fee groups, master heads & amount slabs',
            'color' => '#ea580c',
            'bg_tint' => '#fff7ed'
        ],
        'fees/ledger' => [
            'icon' => 'fa-book',
            'desc' => 'Comprehensive student fee statements & ledgers',
            'color' => '#0284c7',
            'bg_tint' => '#f0f9ff'
        ],
        'ca_report' => [
            'icon' => 'fa-file-text-o',
            'desc' => 'Accountant daily receipts register & invoice logs',
            'color' => '#0d9488',
            'bg_tint' => '#f0fdfa'
        ],
        'fees_cheque' => [
            'icon' => 'fa-check-square-o',
            'desc' => 'Cheque, DD & online transaction approval desk',
            'color' => '#7c3aed',
            'bg_tint' => '#faf5ff'
        ],
        'feesRemainderCron' => [
            'icon' => 'fa-clock-o',
            'desc' => 'Track outstanding dues, arrears & reminder list',
            'color' => '#dc2626',
            'bg_tint' => '#fef2f2'
        ],
        'refund_fees' => [
            'icon' => 'fa-undo',
            'desc' => 'Process student fee cancellations & refund vouchers',
            'color' => '#d97706',
            'bg_tint' => '#fffbeb'
        ],
        'fees/ledger/collect' => [
            'icon' => 'fa-address-card-o',
            'desc' => 'Direct fee collection via student ledger / SR number',
            'color' => '#4f46e5',
            'bg_tint' => '#eef2ff'
        ],
        'hostel/collect/fees' => [
            'icon' => 'fa-bed',
            'desc' => 'Hostel boarding, mess & room fee collection',
            'color' => '#059669',
            'bg_tint' => '#ecfdf5'
        ],
        'hostel/fees/view' => [
            'icon' => 'fa-list-alt',
            'desc' => 'View hostel transaction logs & fee receipts',
            'color' => '#0284c7',
            'bg_tint' => '#f0f9ff'
        ],
        'hostel/student/report' => [
            'icon' => 'fa-user-circle-o',
            'desc' => 'Hostel boarders roster & pending dues list',
            'color' => '#6366f1',
            'bg_tint' => '#eef2ff'
        ],
        'hostel/fees/security_deposite' => [
            'icon' => 'fa-shield',
            'desc' => 'Caution money, hostel deposits & refundable funds',
            'color' => '#c026d3',
            'bg_tint' => '#fdf4ff'
        ]
    ];
@endphp

<style>
/* ========================================================
   FEES DASHBOARD - EXCEL GREEN EXECUTIVE THEME (#217346)
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

.fees-dash-page {
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

/* Chrome / Safari autofill color reset */
.dash-quick-filter input:-webkit-autofill,
.dash-quick-filter input:-webkit-autofill:hover,
.dash-quick-filter input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px rgba(255, 255, 255, 0.15) inset !important;
    -webkit-text-fill-color: #ffffff !important;
    caret-color: #ffffff;
    transition: background-color 5000s ease-in-out 0s;
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

.toggle-password {
    position: absolute;
    right: 18%;
    top: 54%;
    transform: translateY(-50%);
    cursor: pointer;
}
</style>

<div class="content-wrapper fees-dash-page">
    <section class="content pt-2">
        <div class="container-fluid px-2">

            <!-- Main Unified Dashboard Card -->
            <div class="dash-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-money"></i>
                        <span>{{ __('fees.Fees Counter') }}</span>
                        <small>Fee Collection, Assigning, Ledgers &amp; Accounts Dashboard</small>
                    </h1>
                    
                    <div class="dash-title-tools">
                        <div class="dash-quick-filter mr-1">
                            <i class="fa fa-search"></i>
                            <input type="search" id="dashModuleSearch" name="search_fee_module_quick" placeholder="Quick find module..." autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-1p-ignore readonly onfocus="this.removeAttribute('readonly');" onclick="this.removeAttribute('readonly');">
                        </div>
                        <a href="{{ url('Fees/add') }}" class="btn-header-action btn-header-add" title="Collect Fees">
                            <i class="fa fa-plus text-success mr-1"></i> Collect Fees
                        </a>
                        <a href="{{ url('fees_assign') }}" class="btn-header-action" title="Student Fee Assign">
                            <i class="fa fa-users text-primary mr-1"></i> Fee Assign
                        </a>
                        <a href="{{ url('fees/ledger') }}" class="btn-header-action" title="Fees Ledger">
                            <i class="fa fa-table text-info mr-1"></i> Fees Ledger
                        </a>
                        <a href="{{ url('ca_report') }}" class="btn-header-action" title="Fee Receipts">
                            <i class="fa fa-file-text-o text-warning mr-1"></i> Fee Receipts
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
                                <p class="mb-0 font-weight-bold" style="font-size: 13px;">No fees counter permissions assigned to your role.</p>
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

<!-- Counter Authentication Modal -->
<div class="modal fade" id="staticBackdrop" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Counter Authentication</h5>
        <button type="button" style="visibility:hidden" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="counterAuthForm" autocomplete="off" onsubmit="return false;">
          <!-- Hidden inputs to absorb browser autofill -->
          <input type="text" style="display:none !important;" tabindex="-1" autocomplete="username">
          <input type="password" style="display:none !important;" tabindex="-1" autocomplete="current-password">

          <div class="modal-body">
               <table class="text-center table table-bordered table-striped dtr-inline">
                   <thead>
                       <th colspan="50">Counter </th>
                       <th colspan="50">Password </th>
                   </thead>
                   <tbody>
                        <td colspan="50" class="p-3"> 
                            @if(!empty($getCounters))
                                <select name="counter_name" id="counter_id" class="form-control" required>
                                     <option value=""> Select</option>
                                 @foreach($getCounters as $item)
                                 <option value="{{$item->id ?? '' }}"> {{ $item->name}}</option>
                                 @endforeach
                                </select>
                            @endif
                        </td>
                       <td colspan="50" class="p-3" style="position: relative;">
                           <input type="password" id="password" name="counter_auth_pin" class="form-control" autocomplete="new-password" required/>
                           <i class="toggle-password fa fa-eye" onclick="togglePasswordVisibility()"></i>
                       </td>
                   </tbody>
               </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="get_in">Get In</button>
                <a class="text-white" href="{{ URL::previous() }}">
                    <button type="button" class="btn btn-danger">
                        Back
                    </button>
                </a>
          </div>
      </form>
    </div>
  </div>
</div>
  
<button style="visibility:hidden" type="button" class="btn btn-primary" id="modal_id" data-toggle="modal" data-target="#staticBackdrop">
  Open Modal
</button>

<script>
function togglePasswordVisibility() {
  const passwordInput = document.getElementById('password');
  const eyeIcon = document.querySelector('.toggle-password');

  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    eyeIcon.classList.remove('fa-eye');
    eyeIcon.classList.add('fa-eye-slash');
  } else {
    passwordInput.type = 'password';
    eyeIcon.classList.remove('fa-eye-slash');
    eyeIcon.classList.add('fa-eye');
  }
}

$(document).ready(function() {
    var session_counter = "{{ Session::get('counter_id')}}";
    if(session_counter == '') {
        // $("#modal_id").trigger("click");
    }

    // Ensure search input is empty on load and prevent browser autofill
    var searchInput = $('#dashModuleSearch');
    searchInput.val('');
    
    setTimeout(function() {
        if (searchInput.val() !== '') {
            searchInput.val('');
            $('.module-card-item').show();
            $('#filterCountNotice').hide();
            $('#noResultsNotice').hide();
        }
    }, 50);

    setTimeout(function() {
        if (searchInput.val() !== '') {
            searchInput.val('');
            $('.module-card-item').show();
            $('#filterCountNotice').hide();
            $('#noResultsNotice').hide();
        }
    }, 250);

    // Instant module search / filter
    searchInput.on('input', function(){
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

$("#get_in").click(function() {
    var password = $("#password").val();
    var counter_id = $("#counter_id").val();
    var basurl = "{{ url('/') }}";
    $.ajax({
        type: "post",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: basurl + '/check_authentication',
        data: {counter_id: counter_id, password: password},
        success: function(result) {
            if(result == "DONE") {
                $('.close').trigger('click');
            } else {
                toastr.error("Wrong Credentials");
            }
        }
    });
});
</script>

@endsection
