@extends('layout.app')
@section('content')

<style>
/* ========================================================
   AGENTS DIRECTORY & MANAGEMENT - EXECUTIVE THEME (#217346)
   ======================================================== */
:root {
    --ag-green: #217346;
    --ag-green-dark: #165632;
    --ag-green-light: #f0fdf4;
    --ag-green-border: #86efac;
    --ag-border: #cbd5e1;
    --ag-border-dark: #94a3b8;
    --ag-bg: #f8fafc;
    --ag-text: #0f172a;
    --ag-muted: #64748b;
}

.agents-page {
    background-color: #f1f5f9;
    font-size: 12px;
}

.dash-main-card {
    border: 1px solid var(--ag-border) !important;
    border-top: 3px solid var(--ag-green) !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
    background: #ffffff;
    margin-bottom: 14px;
    overflow: hidden;
}

.dash-titlebar {
    min-height: 42px;
    padding: 8px 14px;
    background: var(--ag-green) !important;
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
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dash-title small {
    color: #dcfce7;
    font-size: 11px;
    font-weight: 500;
}

.btn-header-action {
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 3px 10px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
}
.btn-header-action:hover {
    background: #ffffff;
    color: var(--ag-green) !important;
}

/* KPI Cards */
.kpi-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 10px;
    margin-bottom: 12px;
}
.kpi-card {
    background: #ffffff;
    border: 1px solid var(--ag-border);
    border-radius: 4px;
    padding: 10px 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    position: relative;
    overflow: hidden;
}
.kpi-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    width: 4px;
}
.kpi-card.kpi-total::before { background: #3b82f6; }
.kpi-card.kpi-active::before { background: #16a34a; }
.kpi-card.kpi-adm::before { background: #d97706; }
.kpi-card.kpi-enq::before { background: #8b5cf6; }

.kpi-label {
    font-size: 10.5px;
    font-weight: 700;
    color: var(--ag-muted);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
}
.kpi-val {
    font-size: 18px;
    font-weight: 700;
    color: var(--ag-text);
}

/* Filter Card */
.filter-card {
    background: #f8fafc;
    border-bottom: 1px solid var(--ag-border);
    padding: 10px 14px;
}
.form-label-compact {
    font-size: 10.5px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    margin-bottom: 2px;
    display: block;
}
.filter-field {
    height: 30px;
    font-size: 11.5px;
    border: 1px solid var(--ag-border);
    border-radius: 3px;
    padding: 3px 8px;
}
.filter-field:focus {
    border-color: var(--ag-green);
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.15);
}

/* Table */
.agent-table {
    width: 100%;
    margin-bottom: 0;
    font-size: 11.5px;
    border-collapse: collapse;
}
.agent-table th {
    background: var(--ag-green) !important;
    color: #ffffff !important;
    padding: 7px 10px !important;
    font-weight: 700;
    border: 1px solid var(--ag-green-dark) !important;
    white-space: nowrap;
}
.agent-table td {
    padding: 7px 10px !important;
    vertical-align: middle !important;
    border: 1px solid #e2e8f0;
    color: #1e293b;
}
.agent-table tr:hover {
    background-color: var(--ag-green-light);
}

.badge-count {
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.badge-count-adm {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}
.badge-count-enq {
    background: #ede9fe;
    color: #6b21a8;
    border: 1px solid #ddd6fe;
}
</style>

<div class="content-wrapper agents-page">
    <section class="content pt-2 pb-4">
        <div class="container-fluid px-2">
            
            <!-- KPI Summary Cards -->
            <div class="kpi-row">
                <div class="kpi-card kpi-total">
                    <div class="kpi-label"><i class="fa fa-users text-primary mr-1"></i> Total Agents</div>
                    <div class="kpi-val">{{ number_format($totalAgents ?? 0) }}</div>
                </div>
                <div class="kpi-card kpi-active">
                    <div class="kpi-label"><i class="fa fa-check-circle text-success mr-1"></i> Active Agents</div>
                    <div class="kpi-val">{{ number_format($activeAgents ?? 0) }}</div>
                </div>
                <div class="kpi-card kpi-adm">
                    <div class="kpi-label"><i class="fa fa-graduation-cap text-warning mr-1"></i> Total Admissions via Agents</div>
                    <div class="kpi-val">{{ number_format($totalAdmissionsViaAgents ?? 0) }}</div>
                </div>
                <div class="kpi-card kpi-enq">
                    <div class="kpi-label"><i class="fa fa-question-circle text-info mr-1"></i> Total Enquiries via Agents</div>
                    <div class="kpi-val">{{ number_format($totalEnquiriesViaAgents ?? 0) }}</div>
                </div>
            </div>

            <!-- Main Unified Agents Card -->
            <div class="dash-main-card">
                
                <!-- Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-id-badge"></i>
                        <span>Agents / Counselors Directory</span>
                        <small>Manage admission &amp; enquiry agents, referrals &amp; contact details</small>
                    </h1>
                    <div>
                        <button type="button" class="btn btn-sm btn-light font-weight-bold" id="btnOpenAddAgentModal" style="color: var(--ag-green); border-radius: 3px;">
                            <i class="fa fa-plus-circle mr-1"></i> Add New Agent
                        </button>
                    </div>
                </div>

                <!-- Filters Form -->
                <div class="filter-card">
                    <form method="GET" action="{{ url('agents') }}">
                        <div class="row align-items-end mx-n1">
                            <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-12 px-1 mb-1">
                                <label class="form-label-compact"><i class="fa fa-user text-success mr-1"></i> Agent Name</label>
                                <input type="text" name="name" class="form-control filter-field" placeholder="Search by name..." value="{{ $search['name'] ?? '' }}">
                            </div>

                            <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-12 px-1 mb-1">
                                <label class="form-label-compact"><i class="fa fa-phone text-info mr-1"></i> Mobile Number</label>
                                <input type="text" name="mobile" class="form-control filter-field" placeholder="Search by mobile..." value="{{ $search['mobile'] ?? '' }}">
                            </div>

                            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 px-1 mb-1">
                                <label class="form-label-compact"><i class="fa fa-toggle-on text-primary mr-1"></i> Status</label>
                                <select name="status" class="form-control filter-field">
                                    <option value="">All Status</option>
                                    <option value="1" {{ ($search['status'] === '1' || $search['status'] === 1) ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ ($search['status'] === '0' || $search['status'] === 0) ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-md-12 col-sm-6 col-12 px-1 mb-1">
                                <button type="submit" class="btn btn-sm btn-success font-weight-bold" style="background: var(--ag-green); border-color: var(--ag-green-dark); height: 30px; font-size: 11.5px;">
                                    <i class="fa fa-search mr-1"></i> Filter Agents
                                </button>
                                <a href="{{ url('agents') }}" class="btn btn-sm btn-secondary font-weight-bold ml-1" style="height: 30px; font-size: 11.5px;">
                                    <i class="fa fa-refresh mr-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table View -->
                <div class="table-responsive p-0">
                    <table class="agent-table">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">#</th>
                                <th>Agent / Counselor Name</th>
                                <th style="width: 140px;">Mobile Number</th>
                                <th style="width: 180px;">Email</th>
                                <th>Address</th>
                                <th style="width: 110px; text-align: center;">Enquiries</th>
                                <th style="width: 110px; text-align: center;">Admissions</th>
                                <th style="width: 80px; text-align: center;">Status</th>
                                <th style="width: 110px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($agents) && count($agents) > 0)
                                @foreach($agents as $idx => $item)
                                    <tr>
                                        <td style="text-align: center; font-weight: 700; color: #64748b;">
                                            {{ ($agents->currentPage() - 1) * $agents->perPage() + $idx + 1 }}
                                        </td>
                                        <td class="font-weight-bold text-dark">
                                            <i class="fa fa-user-circle text-success mr-1"></i> {{ $item->name ?? '-' }}
                                        </td>
                                        <td>
                                            @if(!empty($item->mobile))
                                                <a href="tel:{{ $item->mobile }}" class="text-dark font-weight-bold">
                                                    <i class="fa fa-phone text-info mr-1"></i> {{ $item->mobile }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($item->email))
                                                <a href="mailto:{{ $item->email }}" class="text-secondary">
                                                    <i class="fa fa-envelope text-primary mr-1"></i> {{ $item->email }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-muted">
                                            {{ $item->address ?: '-' }}
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{ url('enquiryView') }}?agent_id={{ $item->id }}" class="badge-count badge-count-enq" title="View enquiries referred by this agent">
                                                <i class="fa fa-question-circle"></i> {{ $item->enquiries_count ?? 0 }}
                                            </a>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{ url('admissionView') }}?agent_id={{ $item->id }}" class="badge-count badge-count-adm" title="View admissions referred by this agent">
                                                <i class="fa fa-graduation-cap"></i> {{ $item->admissions_count ?? 0 }}
                                            </a>
                                        </td>
                                        <td style="text-align: center;">
                                            @if($item->status == 1)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn btn-xs btn-outline-primary btnEditAgent" 
                                                    data-id="{{ $item->id }}"
                                                    data-name="{{ $item->name }}"
                                                    data-mobile="{{ $item->mobile }}"
                                                    data-email="{{ $item->email }}"
                                                    data-address="{{ $item->address }}"
                                                    data-status="{{ $item->status }}"
                                                    title="Edit Agent">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            
                                            <form method="POST" action="{{ url('agents/delete') }}" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this agent?');">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $item->id }}">
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete Agent">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="9" class="text-center text-muted p-4">
                                        <i class="fa fa-info-circle mr-1"></i> No agents found. Click "Add New Agent" above to create your first agent/counselor.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if(!empty($agents) && $agents->hasPages())
                    <div class="p-2 border-top bg-light d-flex justify-content-end">
                        {{ $agents->links() }}
                    </div>
                @endif

            </div>

        </div>
    </section>
</div>

<!-- Modal: Add / Edit Agent -->
<div class="modal fade" id="agentModal" tabindex="-1" role="dialog" aria-labelledby="agentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px;">
        <div class="modal-content" style="border: 1px solid #9eacb7; border-radius: 6px; box-shadow: 0 10px 30px rgba(0,0,0,0.25);">
            <div class="modal-header" style="background: var(--ag-green); color: #ffffff; padding: 10px 14px;">
                <h6 class="modal-title font-weight-bold text-white mb-0" id="agentModalLabel">
                    <i class="fa fa-user-plus mr-1"></i> Add New Agent / Counselor
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ url('agents/store') }}">
                @csrf
                <input type="hidden" name="id" id="agent_modal_id" value="">
                
                <div class="modal-body p-3" style="font-size: 12px; background: #ffffff;">
                    <!-- Name -->
                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-dark mb-1" style="font-size: 11px;">
                            <i class="fa fa-user text-success mr-1"></i> Agent / Counselor Full Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="agent_modal_name" class="form-control form-control-sm" placeholder="e.g. Ramesh Kumar, Sharma Consultancy..." required autocomplete="off" style="font-size: 12px; height: 32px;">
                    </div>

                    <!-- Mobile & Email -->
                    <div class="row mx-n1 mb-2">
                        <div class="col-6 px-1">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 11px;">
                                <i class="fa fa-phone text-info mr-1"></i> Mobile Number
                            </label>
                            <input type="text" name="mobile" id="agent_modal_mobile" class="form-control form-control-sm" placeholder="10-digit mobile" maxlength="15" style="font-size: 12px; height: 32px;">
                        </div>
                        <div class="col-6 px-1">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 11px;">
                                <i class="fa fa-envelope text-primary mr-1"></i> Email
                            </label>
                            <input type="email" name="email" id="agent_modal_email" class="form-control form-control-sm" placeholder="agent@email.com" style="font-size: 12px; height: 32px;">
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-dark mb-1" style="font-size: 11px;">
                            <i class="fa fa-map-marker text-danger mr-1"></i> Address / City
                        </label>
                        <textarea name="address" id="agent_modal_address" class="form-control form-control-sm" rows="2" placeholder="Full address or city location..." style="font-size: 11.5px;"></textarea>
                    </div>

                    <!-- Status -->
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark mb-1" style="font-size: 11px;">Status</label>
                        <select name="status" id="agent_modal_status" class="form-control form-control-sm" style="font-size: 11.5px; height: 32px;">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer p-2" style="background: #f1f4f6; border-top: 1px solid #cbd5e1;">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success font-weight-bold" style="background: var(--ag-green); border-color: var(--ag-green-dark); padding: 4px 14px;">
                        <i class="fa fa-check-circle mr-1"></i> Save Agent
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#btnOpenAddAgentModal').on('click', function() {
        $('#agentModalLabel').html('<i class="fa fa-user-plus mr-1"></i> Add New Agent / Counselor');
        $('#agent_modal_id').val('');
        $('#agent_modal_name').val('');
        $('#agent_modal_mobile').val('');
        $('#agent_modal_email').val('');
        $('#agent_modal_address').val('');
        $('#agent_modal_status').val('1');
        $('#agentModal').modal('show');
    });

    $(document).on('click', '.btnEditAgent', function() {
        var $btn = $(this);
        $('#agentModalLabel').html('<i class="fa fa-edit mr-1"></i> Edit Agent / Counselor');
        $('#agent_modal_id').val($btn.data('id'));
        $('#agent_modal_name').val($btn.data('name'));
        $('#agent_modal_mobile').val($btn.data('mobile'));
        $('#agent_modal_email').val($btn.data('email'));
        $('#agent_modal_address').val($btn.data('address'));
        $('#agent_modal_status').val($btn.data('status'));
        $('#agentModal').modal('show');
    });
});
</script>

@endsection
