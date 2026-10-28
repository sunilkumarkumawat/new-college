@php
    $getPermission = Helper::getPermission();
    $roleId = Session::get('role_id') ?? 1;
    $totalCount = !empty($data) ? count($data) : 0;
@endphp

@extends('layout.app') 
@section('content')

<style>
/* ========================================================
   EXECUTIVE SETTINGS VIEW THEME (Excel Green System)
   ======================================================== */
:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #f0fdf4;
    --green-excel-border: #a7d0b8;
    --line: #b8c8d5;
    --line-subtle: #cbd5e1;
    --navy: #002c54;
    --text-main: #0f172a;
}

.setting-view-page {
    font-size: 11.5px;
    color: var(--text-main);
    background-color: #f4f6f9;
}

/* 1. Main Form Card Wrapper */
.dash-main-card {
    border: 1px solid var(--line) !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
    background: #ffffff;
    margin-bottom: 16px;
    overflow: hidden;
}

/* 2. Executive Titlebar */
.dash-titlebar {
    min-height: 42px;
    padding: 6px 14px;
    background: var(--green-excel);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
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
}

.btn-header-action {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 3px 10px !important;
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
}

.btn-header-add {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    color: #166534 !important;
    font-weight: 700 !important;
}

.btn-header-add:hover {
    background: #dcfce7 !important;
    color: #14532d !important;
}

/* 3. Metrics & Search Toolbar */
.setting-toolbar {
    background: #f8fafc;
    border-bottom: 1px solid var(--line-subtle);
    padding: 8px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.metric-badge-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.metric-pill {
    background: #ffffff;
    border: 1px solid var(--line-subtle);
    border-radius: 12px;
    padding: 2px 10px;
    font-size: 11px;
    font-weight: 600;
    color: #475569;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.metric-pill strong {
    color: var(--green-excel);
    font-weight: 700;
}

.search-box-wrapper {
    position: relative;
    min-width: 240px;
}

.search-box-wrapper input {
    height: 30px;
    border-radius: 3px;
    border: 1px solid var(--line-subtle);
    padding: 2px 8px 2px 28px;
    font-size: 11.5px;
    width: 100%;
    transition: all 0.15s ease-in-out;
}

.search-box-wrapper input:focus {
    border-color: var(--green-excel);
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.18);
    outline: none;
}

.search-box-wrapper .search-icon {
    position: absolute;
    left: 9px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 11px;
}

/* 4. Table Styling */
.table-setting-master {
    width: 100%;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.table-setting-master thead th {
    background: #217346 !important;
    color: #ffffff !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 8px 10px !important;
    border: none !important;
    vertical-align: middle;
    white-space: nowrap;
}

.table-setting-master tbody td {
    padding: 8px 10px !important;
    font-size: 11.5px;
    color: #1e293b;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}

.table-setting-master tbody tr:nth-child(even) {
    background-color: #f8fafc;
}

.table-setting-master tbody tr:hover {
    background-color: #f0fdf4 !important;
}

/* Image Thumbnail Cards */
.img-thumb-frame {
    width: 90px;
    height: 42px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 2px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}

.img-thumb-frame img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

/* Action Buttons */
.btn-row-action {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-action-edit {
    background: #e0f2fe !important;
    color: #0369a1 !important;
    border: 1px solid #bae6fd !important;
    border-radius: 3px;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out;
}

.btn-action-edit:hover {
    background: #0284c7 !important;
    color: #ffffff !important;
    border-color: #0284c7 !important;
}

.btn-action-delete {
    background: #fef2f2 !important;
    color: #b91c1c !important;
    border: 1px solid #fecaca !important;
    border-radius: 3px;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out;
}

.btn-action-delete:hover {
    background: #dc2626 !important;
    color: #ffffff !important;
    border-color: #dc2626 !important;
}

/* Empty State */
.empty-state-container {
    padding: 32px 16px;
    text-align: center;
    background: #ffffff;
}

.empty-state-icon {
    font-size: 38px;
    color: #94a3b8;
    margin-bottom: 8px;
}

.empty-state-text {
    font-size: 12.5px;
    font-weight: 600;
    color: #475569;
}
</style>

<div class="content-wrapper setting-view-page">
    <section class="content pt-2 pb-3">
        <div class="container-fluid px-2">
            
            <div class="card dash-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-cogs"></i>
                        <span>{{ __('setting.View Setting') }}</span>
                        <small>| System Profile & Institution Settings Overview</small>
                    </h1>
                    <div class="dash-title-tools">
                        @if(Session::get('role_id') == 1)
                            <a href="{{ url('addSetting') }}" class="btn-header-action btn-header-add" title="Add New Setting">
                                <i class="fa fa-plus mr-1"></i> {{ __('common.Add') }} Setting
                            </a>
                        @endif
                        <a href="{{ url('settings_dashboard') }}" class="btn-header-action" title="Back to Settings Dashboard">
                            <i class="fa fa-arrow-left mr-1"></i> {{ __('common.Back') }}
                        </a>
                    </div>
                </div>

                <!-- 2. Metrics & Search Toolbar -->
                <div class="setting-toolbar">
                    <div class="metric-badge-group">
                        <span class="metric-pill">
                            <i class="fa fa-list-alt text-success"></i> Registered Profiles: <strong id="totalSettingCount">{{ $totalCount }}</strong>
                        </span>
                        <span class="metric-pill">
                            <i class="fa fa-building text-primary"></i> Active Branch: <strong>{{ Session::get('branch_name') ?? 'Default' }}</strong>
                        </span>
                    </div>

                    <div class="search-box-wrapper">
                        <i class="fa fa-search search-icon"></i>
                        <input type="text" id="settingSearchInput" placeholder="Quick search setting by name, phone, email..." autocomplete="off">
                    </div>
                </div>

                <!-- 3. Table Container -->
                <div class="table-responsive">
                    <table class="table table-setting-master mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 45px;">{{ __('common.SR.NO') }}</th>
                                <th style="width: 110px;">{{ __('setting.Logo') }}</th>
                                <th>{{ __('common.Name') }}</th>
                                <th>{{ __('common.E-Mail') }}</th>
                                <th>{{ __('common.Mobile') }}</th>
                                <th>{{ __('common.Address') }}</th>
                                <th>{{ __('setting.Pin Code') }}</th>
                                <th style="width: 110px;">{{ __('setting.Seal & Sign.') }}</th>
                                @if($getPermission->edit == 1 || $getPermission->deletes == 1)
                                    <th class="text-center" style="width: 90px;">{{ __('common.Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="settingTableBody">
                            @if(!empty($data) && count($data) > 0)
                                @php $i = 1; @endphp
                                @foreach ($data as $item)
                                    <tr class="setting-row">
                                        <td class="text-center font-weight-bold text-muted">{{ $i++ }}</td>
                                        <td>
                                            <div class="img-thumb-frame">
                                                <img src="{{ env('IMAGE_SHOW_PATH').'setting/left_logo/'.$item['left_logo'] }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/no_image.png' }}'" alt="Left Logo">
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-dark">{{ $item['name'] ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            <span class="text-primary font-weight-600"><i class="fa fa-envelope-o mr-1 text-muted"></i>{{ $item['gmail'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-dark"><i class="fa fa-phone mr-1 text-muted"></i>{{ $item['mobile'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-secondary"><i class="fa fa-map-marker mr-1 text-muted"></i>{{ $item['address'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border text-dark px-2 py-1">{{ $item['pincode'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <div class="img-thumb-frame">
                                                <img src="{{ env('IMAGE_SHOW_PATH').'setting/seal_sign/'.$item['seal_sign'] }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/no_image.png' }}'" alt="Seal & Sign">
                                            </div>
                                        </td>
                                        @if($getPermission->edit == 1 || $getPermission->deletes == 1)
                                            <td class="text-center">
                                                <div class="btn-row-action">
                                                    @if($getPermission->edit == 1)
                                                        <a href="{{ url('editSetting', $item->id) }}" class="btn-action-edit" title="Edit Setting">
                                                            <i class="fa fa-edit"></i> Edit
                                                        </a>
                                                    @endif
                                                    @if($item->id !== 1 && Session::get('role_id') == 1 && $getPermission->deletes == 1)
                                                        <button type="button" class="btn-action-delete deleteData" data-id="{{ $item->id }}" data-name="{{ $item->name ?? '' }}" data-toggle="modal" data-target="#Modal_id" title="Delete Setting">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                <tr id="emptyRow">
                                    <td colspan="{{ ($getPermission->edit == 1 || $getPermission->deletes == 1) ? 9 : 8 }}">
                                        <div class="empty-state-container">
                                            <i class="fa fa-cogs empty-state-icon"></i>
                                            <div class="empty-state-text">No Setting Configurations Found</div>
                                            <p class="text-muted small mb-0 mt-1">Configure your branch institution profile to get started.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            <tr id="emptySearchRow" style="display: none;">
                                <td colspan="{{ ($getPermission->edit == 1 || $getPermission->deletes == 1) ? 9 : 8 }}">
                                    <div class="empty-state-container">
                                        <i class="fa fa-search empty-state-icon"></i>
                                        <div class="empty-state-text">No Matching Settings Found</div>
                                        <p class="text-muted small mb-0 mt-1">Try adjusting your search criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </section>
</div>

<!-- Executive Delete Confirmation Modal -->
<div class="modal fade" id="Modal_id" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 6px; overflow: hidden;">
            <div class="modal-header bg-danger text-white py-2 px-3">
                <h5 class="modal-title font-weight-bold" style="font-size: 14px;">
                    <i class="fa fa-exclamation-triangle mr-2"></i> {{ __('common.Delete Confirmation') }}
                </h5>
                <button type="button" class="close text-white opacity-8" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('deleteSetting') }}" method="post">
                @csrf
                <div class="modal-body text-center py-4 px-3">
                    <input type="hidden" id="delete_id" name="delete_id">
                    <div class="mb-3">
                        <i class="fa fa-trash text-danger" style="font-size: 44px; opacity: 0.85;"></i>
                    </div>
                    <h6 class="font-weight-bold text-dark mb-1">{{ __('common.Are you sure you want to delete') }}?</h6>
                    <p class="text-muted small mb-0" id="deleteSettingTargetName">This action will permanently erase the selected setting profile.</p>
                </div>
                <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-600 px-3" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i> {{ __('common.Close') }}
                    </button>
                    <button type="submit" class="btn btn-sm btn-danger font-weight-600 px-3">
                        <i class="fa fa-check mr-1"></i> {{ __('common.Delete') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script>
    $(document).ready(function(){
        // Real-time live filter
        $('#settingSearchInput').on('keyup search input', function(){
            var value = $(this).val().toLowerCase().trim();
            var visibleCount = 0;

            $('#settingTableBody tr.setting-row').each(function(){
                var rowText = $(this).text().toLowerCase();
                if(rowText.indexOf(value) > -1) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            if (visibleCount === 0 && $('#settingTableBody tr.setting-row').length > 0) {
                $('#emptySearchRow').show();
            } else {
                $('#emptySearchRow').hide();
            }
            $('#totalSettingCount').text(visibleCount);
        });

        // Delete Modal target setter
        $(document).on('click', '.deleteData', function(){
            var delete_id = $(this).data('id');
            var targetName = $(this).data('name');
            $('#delete_id').val(delete_id);
            if (targetName) {
                $('#deleteSettingTargetName').text('Are you sure you want to remove profile for "' + targetName + '"?');
            }
        });
    });
</script>

@endsection