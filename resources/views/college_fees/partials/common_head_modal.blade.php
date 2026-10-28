<!-- ========================================================
     MODAL: COMMON FEE HEAD MASTER
     Theme: Excel Spreadsheet & Promote Students Layout
     Standard: Clean, Professional English Language
     ======================================================== -->
<style>
#commonHeadModal .modal-content {
    border: 1px solid #9eacb7 !important;
    border-radius: 2px !important;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.22) !important;
}

#commonHeadModal .modal-header {
    padding: 8px 14px !important;
    background: #f1f4f6 !important;
    color: #1e2d39 !important;
    border-bottom: 1px solid #aebbc5 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

#commonHeadModal .modal-title {
    margin: 0 !important;
    color: #1e2d39 !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    display: flex;
    align-items: center;
}

#commonHeadModal .modal-title i {
    color: #217346 !important;
    font-size: 14px !important;
    margin-right: 7px;
}

#commonHeadModal .modal-header .close {
    margin: 0 !important;
    padding: 2px 6px !important;
    color: #344450 !important;
    opacity: 0.85 !important;
    font-size: 20px !important;
    background: transparent !important;
    border: 0 !important;
    cursor: pointer !important;
}

#commonHeadModal .modal-header .close:hover {
    color: #000000 !important;
    opacity: 1 !important;
}

#commonHeadModal .modal-body {
    padding: 12px 14px !important;
    background: #ffffff !important;
}

#commonHeadModal .modal-footer {
    padding: 8px 14px !important;
    background: #f1f4f6 !important;
    border-top: 1px solid #cbd5e1 !important;
}

/* Form Panel Matching Promote Page Theme */
.fee-head-create-panel {
    background: #e7eff5;
    border: 1px solid #b8c9d9;
    border-radius: 2px;
    padding: 10px 12px;
    margin-bottom: 12px;
}

.fee-head-create-panel label {
    font-size: 10.5px;
    font-weight: 700;
    margin-bottom: 2px;
    color: #1e293b;
    display: block;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    line-height: 1.1;
}

.fee-head-control {
    height: 29px !important;
    min-height: 29px !important;
    padding: 3px 8px !important;
    font-size: 11.5px !important;
    color: #172033 !important;
    background: #ffffff !important;
    border: 1px solid #b8c4d1 !important;
    border-radius: 2px !important;
    width: 100%;
}

.fee-head-control:focus {
    border-color: #217346 !important;
    box-shadow: 0 0 0 1px rgba(33, 115, 70, 0.25) !important;
    outline: none;
}

.btn-save-fee-head {
    height: 29px !important;
    padding: 3px 12px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    background: #217346 !important;
    border: 1px solid #185c37 !important;
    color: #ffffff !important;
    border-radius: 2px !important;
    transition: all 0.15s ease-in-out;
}

.btn-save-fee-head:hover {
    background: #185c37 !important;
    color: #ffffff !important;
}

/* Master Table Styling */
.table-fee-heads th {
    background: #d9eaf7 !important;
    color: #152536 !important;
    border-bottom: 2px solid #8fa7b8 !important;
    border-top: none !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 6px 8px !important;
    border-color: #8fa7b8 !important;
}

.table-fee-heads td {
    padding: 5px 8px !important;
    vertical-align: middle !important;
    font-size: 11.5px !important;
    border-color: #cbd5e1 !important;
}

.table-fee-heads tbody tr:hover {
    background-color: #f8fafc;
}
</style>

<div class="modal fade" id="commonHeadModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 680px;">
        <div class="modal-content">
            
            <!-- Modal Header (Theme Layout Matching Promote Students Page) -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-cubes"></i> Common Fee Head Master
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                
                <!-- Create Reusable Fee Head Panel -->
                <form id="formAddCommonHead" class="fee-head-create-panel">
                    @csrf
                    
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa fa-plus-circle text-success mr-2" style="font-size: 13px;"></i>
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #1e293b; letter-spacing: 0.35px;">
                            Create New Master Fee Head
                        </span>
                        <span class="ml-auto text-muted" style="font-size: 10px;">
                            <i class="fa fa-info-circle mr-1 text-primary"></i> Available for all batches and courses
                        </span>
                    </div>

                    <div class="alert alert-light border py-1 px-2 mb-2 d-flex align-items-center" style="font-size: 10.5px; background: #fffbeb; border-color: #fef08a !important; color: #92400e; border-radius: 2px;">
                        <i class="fa fa-lock mr-1 text-warning"></i>
                        <span><strong>System Policy:</strong> Tuition Fees &amp; Previous Dues are permanent system core fee heads (Locked &mdash; non-editable &amp; non-deletable).</span>
                    </div>

                    <div class="row align-items-end g-2">
                        <!-- Fee Head Name -->
                        <div class="col-md-5 col-12 mb-2 mb-md-0">
                            <label>Fee Head Name <span class="text-danger">*</span></label>
                            <input type="text" name="head_name" id="comm_head_name" class="fee-head-control font-weight-bold" placeholder="e.g. Tuition Fee, Exam Fee" required autofocus>
                        </div>

                        <!-- Fee Type Dropdown -->
                        <div class="col-md-4 col-7 mb-2 mb-md-0">
                            <label>Fee Type <span class="text-danger">*</span></label>
                            <select name="group_type" id="comm_group_type" class="fee-head-control" required>
                                <option value="Tuition">Tuition / Academic Fee</option>
                                <option value="Examination">Examination Fee</option>
                                <option value="Admission & Registration">Admission & Registration Fee</option>
                                <option value="Laboratory">Laboratory & Practical Fee</option>
                                <option value="Library">Library Fee</option>
                                <option value="Hostel & Mess">Hostel & Mess Charges</option>
                                <option value="Transport">Transport / Conveyance Fee</option>
                                <option value="Sports & Cultural">Sports & Cultural Activity Fee</option>
                                <option value="Development Charges">Development & Infrastructure</option>
                                <option value="Caution Money">Caution Money / Security Deposit</option>
                                <option value="Optional">Optional / Additional Course Fee</option>
                                <option value="Miscellaneous">Miscellaneous / Other Charges</option>
                            </select>
                        </div>

                        <!-- Action Submit Button -->
                        <div class="col-md-3 col-5">
                            <button type="submit" id="btnSaveCommonHead" class="btn btn-save-fee-head btn-block">
                                <i class="fa fa-plus mr-1"></i> Save Head
                            </button>
                        </div>
                    </div>

                    <!-- Optional Description Field -->
                    <div class="row mt-2">
                        <div class="col-12">
                            <label class="text-muted" style="font-size: 10px; font-weight: 600;">Description / Remarks (Optional)</label>
                            <input type="text" name="description" id="comm_description" class="fee-head-control" placeholder="Optional notes (e.g. Applicable for annual curriculum, Non-refundable)" style="height: 27px !important; font-size: 11px;">
                        </div>
                    </div>
                </form>

                <!-- Configured Master Fee Heads Toolbar -->
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <span class="font-weight-bold text-dark" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.35px;">
                        <i class="fa fa-list text-success mr-1"></i> Configured Fee Heads (<span id="lblCommonHeadCount">0</span>)
                    </span>
                    <div class="d-flex align-items-center" style="gap: 6px;">
                        <input type="text" id="searchCommonHead" onkeyup="filterCommonHeadsTable()" placeholder="Search fee heads..." class="fee-head-control py-0 px-2" style="width: 150px; height: 26px; font-size: 11px;">
                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="loadCommonHeadsLibrary()" title="Refresh List" style="padding: 2px 7px; height: 26px; border-radius: 2px;">
                            <i class="fa fa-refresh"></i>
                        </button>
                    </div>
                </div>

                <!-- Configured Master Fee Heads Table -->
                <div class="table-responsive border" style="max-height: 270px; overflow-y: auto; border-color: #cbd5e1 !important;">
                    <table class="table table-sm table-bordered table-hover mb-0 table-fee-heads" id="tblCommonHeads">
                        <thead>
                            <tr>
                                <th style="width: 45px; text-align: center;">#</th>
                                <th>Fee Head Name</th>
                                <th style="width: 180px; text-align: center;">Fee Type</th>
                                <th class="text-center" style="width: 65px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="commonHeadsLibraryBody">
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">
                                    <i class="fa fa-spinner fa-spin mr-1"></i> Loading fee heads...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Modal Footer (Matching Promote Students Page) -->
            <div class="modal-footer d-flex justify-content-between align-items-center">
                <span class="text-muted" style="font-size: 11px;">
                    <i class="fa fa-info-circle mr-1 text-primary"></i> Click 'Save Head' to add to master library.
                </span>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal" style="font-size: 11.5px; padding: 4px 14px; border-radius: 2px;">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

<script>
// ========================================================
// COMMON FEE HEAD MASTER JAVASCRIPT LOGIC (CLEAN & PROFESSIONAL)
// ========================================================
function openCommonHeadModal() {
    $('#comm_head_name').val('');
    $('#comm_description').val('');
    $('#comm_group_type').val('Tuition');
    $('#commonHeadModal').modal('show');
    loadCommonHeadsLibrary();
    setTimeout(function() {
        $('#comm_head_name').focus();
    }, 350);
}

// Notification Helper
function showCommonToast(type, msg) {
    if (typeof showToast === 'function') {
        showToast(type, msg);
    } else if (typeof toastr !== 'undefined') {
        toastr[type](msg);
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type === 'error' ? 'error' : 'success',
            title: msg,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        alert(msg);
    }
}

// Fee Type Badge Formatter
function isCoreSystemHead(name) {
    if (!name) return false;
    var lower = name.toLowerCase().trim();
    return lower.indexOf('tuition') !== -1 || 
           lower.indexOf('tution') !== -1 || 
           lower.indexOf('previous dues') !== -1 || 
           lower.indexOf('prev dues') !== -1 || 
           lower.indexOf('previous fee') !== -1 || 
           lower.indexOf('prev fee') !== -1;
}

function getFeeTypeBadge(type) {
    if (!type) type = 'Tuition';
    var lower = type.toLowerCase();
    var bg = '#f1f5f9', color = '#334155', border = '#cbd5e1';
    
    if (lower.indexOf('tuition') !== -1 || lower.indexOf('academic') !== -1 || lower.indexOf('compulsory') !== -1) {
        bg = '#eff6ff'; color = '#1d4ed8'; border = '#bfdbfe';
    } else if (lower.indexOf('exam') !== -1) {
        bg = '#fef3c7'; color = '#b45309'; border = '#fde68a';
    } else if (lower.indexOf('library') !== -1) {
        bg = '#ecfeff'; color = '#0e7490'; border = '#a5f3fc';
    } else if (lower.indexOf('hostel') !== -1) {
        bg = '#f5f3ff'; color = '#6d28d9'; border = '#ddd6fe';
    } else if (lower.indexOf('transport') !== -1) {
        bg = '#f0fdfa'; color = '#0f766e'; border = '#99f6e4';
    } else if (lower.indexOf('lab') !== -1) {
        bg = '#fff7ed'; color = '#c2410c'; border = '#fed7aa';
    } else if (lower.indexOf('caution') !== -1 || lower.indexOf('security') !== -1) {
        bg = '#fef2f2'; color = '#b91c1c'; border = '#fecaca';
    } else if (lower.indexOf('sports') !== -1 || lower.indexOf('cultural') !== -1) {
        bg = '#f0fdf4'; color = '#15803d'; border = '#bbf7d0';
    } else if (lower.indexOf('admission') !== -1 || lower.indexOf('registration') !== -1) {
        bg = '#fdf4ff'; color = '#a21caf'; border = '#f5d0fe';
    } else if (lower.indexOf('development') !== -1) {
        bg = '#f8fafc'; color = '#475569'; border = '#cbd5e1';
    }
    
    return '<span style="display: inline-block; padding: 2px 8px; font-size: 10.5px; font-weight: 600; border-radius: 3px; background: ' + bg + '; color: ' + color + '; border: 1px solid ' + border + ';">' + type + '</span>';
}

// AJAX: Save Fee Head
$('#formAddCommonHead').on('submit', function(e) {
    e.preventDefault();
    var name = $.trim($('#comm_head_name').val());
    if (!name) {
        showCommonToast('error', 'Please enter a valid Fee Head Name.');
        return;
    }

    if (isCoreSystemHead(name)) {
        showCommonToast('error', 'Fee Head "' + name + '" is a protected System Core fee head. Tuition Fees and Previous Dues cannot be edited, modified or overwritten.');
        return;
    }

    var btn = $('#btnSaveCommonHead');
    var originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Saving...');

    $.ajax({
        url: "{{ route('college.fees.structures.save_common') }}",
        type: "POST",
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false).html(originalText);
            showCommonToast('success', res.message || 'Fee Head saved successfully.');
            
            // Reset input fields
            $('#comm_head_name').val('').focus();
            $('#comm_description').val('');
            $('#comm_group_type').val('Tuition');

            // Refresh library table
            loadCommonHeadsLibrary();
        },
        error: function(xhr) {
            btn.prop('disabled', false).html(originalText);
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred while saving the fee head.';
            showCommonToast('error', msg);
        }
    });
});

// Load Configured Fee Heads Library
var cachedCommonHeads = [];
function loadCommonHeadsLibrary() {
    $('#commonHeadsLibraryBody').html('<tr><td colspan="4" class="text-center py-3 text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Loading fee heads...</td></tr>');
    
    $.ajax({
        url: "{{ route('college.fees.structures.common_heads') }}",
        type: "GET",
        dataType: 'json',
        success: function(res) {
            var heads = res.common_heads || [];
            cachedCommonHeads = heads;
            $('#lblCommonHeadCount').text(heads.length);
            
            // Update Datalist on page for auto-complete
            updateCommonHeadsDatalist(heads);

            if (heads.length === 0) {
                $('#commonHeadsLibraryBody').html('<tr><td colspan="4" class="text-center py-3 text-muted">No master fee heads configured yet.<br><small class="text-muted">Use the form above to create your first fee head.</small></td></tr>');
                return;
            }
            renderCommonHeadsTable(heads);
        },
        error: function() {
            $('#commonHeadsLibraryBody').html('<tr><td colspan="4" class="text-center text-danger py-3">Failed to load fee heads library.</td></tr>');
        }
    });
}

// Render Table Rows
function renderCommonHeadsTable(heads) {
    var rows = '';
    heads.forEach(function(h, idx) {
        var isCore = isCoreSystemHead(h.head_name);
        var feeTypeBadge = getFeeTypeBadge(h.fee_type || h.group_type || 'Tuition');
        var coreBadge = isCore 
            ? ' <span class="badge badge-warning ml-1" style="font-size: 9px; font-weight: 700; background: #fef3c7; color: #92400e; border: 1px solid #fde68a;" title="Permanent core fee head (non-editable & non-deletable)"><i class="fa fa-lock mr-1"></i>Permanent Core</span>'
            : '';
        var actionHtml = isCore
            ? '<span class="badge badge-light border text-muted px-2 py-1 font-weight-bold" style="font-size: 10px; background: #f8fafc; border-color: #cbd5e1 !important;" title="Permanent System Core Head - Cannot be deleted or edited"><i class="fa fa-lock text-warning mr-1"></i> System Core</span>'
            : '<button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteCommonHead(' + h.id + ')" title="Delete Fee Head" style="padding: 2px 7px; height: 24px; border-radius: 2px;"><i class="fa fa-trash"></i></button>';

        rows += '<tr class="head-item-row" data-name="' + (h.head_name || '').toLowerCase() + '" data-type="' + (h.fee_type || '').toLowerCase() + '">' +
            '<td class="text-center text-muted align-middle">' + (idx + 1) + '</td>' +
            '<td class="align-middle"><strong class="text-dark">' + h.head_name + '</strong>' + coreBadge + (h.description ? '<br><small class="text-muted" style="font-size: 10px;">' + h.description + '</small>' : '') + '</td>' +
            '<td class="text-center align-middle">' + feeTypeBadge + '</td>' +
            '<td class="text-center align-middle">' + actionHtml + '</td>' +
        '</tr>';
    });
    $('#commonHeadsLibraryBody').html(rows);
}

// Search Filter in Table
function filterCommonHeadsTable() {
    var query = $.trim($('#searchCommonHead').val()).toLowerCase();
    if (!query) {
        $('.head-item-row').show();
        return;
    }
    $('.head-item-row').each(function() {
        var name = $(this).attr('data-name') || '';
        var type = $(this).attr('data-type') || '';
        if (name.indexOf(query) !== -1 || type.indexOf(query) !== -1) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

// Delete Fee Head
function deleteCommonHead(id) {
    var head = cachedCommonHeads.find(function(x) { return x.id == id; });
    if (head && isCoreSystemHead(head.head_name)) {
        showCommonToast('error', 'Fee Head "' + head.head_name + '" is a protected System Core fee head and cannot be deleted.');
        return;
    }

    if (!confirm('Are you sure you want to delete this fee head from the master library?')) {
        return;
    }
    $.ajax({
        url: "{{ url('/college/fees/structures/delete-common') }}/" + id,
        type: "POST",
        data: { _token: "{{ csrf_token() }}" },
        dataType: 'json',
        success: function(res) {
            showCommonToast('success', res.message || 'Fee Head deleted successfully.');
            loadCommonHeadsLibrary();
        },
        error: function(xhr) {
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Unable to delete the fee head.';
            showCommonToast('error', msg);
        }
    });
}

// Update Datalist on page for auto-complete in Add Head modal
function updateCommonHeadsDatalist(heads) {
    var dl = $('#commonFeeHeadsDatalist');
    if (dl.length === 0) {
        $('body').append('<datalist id="commonFeeHeadsDatalist"></datalist>');
        dl = $('#commonFeeHeadsDatalist');
    }
    dl.empty();
    heads.forEach(function(h) {
        dl.append('<option value="' + h.head_name + '">');
    });

    if ($('#input_head_name').length && !$('#input_head_name').attr('list')) {
        $('#input_head_name').attr('list', 'commonFeeHeadsDatalist');
    }
}

// Initialize on page load
$(document).ready(function() {
    setTimeout(function() {
        loadCommonHeadsLibrary();
    }, 400);
});
</script>
