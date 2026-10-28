<!-- Quick Add Agent Modal Component -->
<div class="modal fade" id="quickAgentModal" tabindex="-1" role="dialog" aria-labelledby="quickAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 420px;">
        <div class="modal-content" style="border: 1px solid #9aa7b1; border-radius: 4px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: #217346; color: #ffffff; padding: 8px 14px; border-bottom: 1px solid #185c37;">
                <h5 class="modal-title" id="quickAgentModalLabel" style="font-size: 13.5px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 6px;">
                    <i class="fa fa-user-plus text-warning"></i> Quick Add Agent / Referrer
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9; font-size: 18px; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickAgentForm" onsubmit="handleQuickAgentSubmit(event)">
                <div class="modal-body p-3" style="font-size: 11.5px; background: #ffffff;">
                    <div id="quickAgentAlert" class="alert alert-danger py-1 px-2 mb-2 d-none" style="font-size: 11px;"></div>
                    
                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-dark mb-1" style="font-size: 11px;">
                            Agent Name <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-user text-muted"></i></span>
                            </div>
                            <input type="text" class="form-control form-control-sm font-weight-bold" id="quick_agent_name" name="name" placeholder="Enter agent full name" required autofocus>
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-dark mb-1" style="font-size: 11px;">
                            Mobile Number <small class="text-muted">(Optional)</small>
                        </label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-phone text-muted"></i></span>
                            </div>
                            <input type="tel" class="form-control form-control-sm" id="quick_agent_mobile" name="mobile" placeholder="10-digit mobile" maxlength="10">
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-2" style="background: #f8fafc; border-top: 1px solid #cbd5e1;">
                    <button type="button" class="btn btn-sm btn-light border px-3" data-dismiss="modal" data-bs-dismiss="modal" style="font-size: 11.5px; font-weight: 600;">
                        Cancel
                    </button>
                    <button type="submit" id="quickAgentSaveBtn" class="btn btn-sm text-white px-3" style="background: #217346; font-size: 11.5px; font-weight: 700;">
                        <i class="fa fa-check mr-1"></i> Save & Select
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleQuickAgentSubmit(e) {
    e.preventDefault();
    var name = $('#quick_agent_name').val().trim();
    var mobile = $('#quick_agent_mobile').val().trim();
    var alertBox = $('#quickAgentAlert');
    var saveBtn = $('#quickAgentSaveBtn');

    if (!name) {
        alertBox.removeClass('d-none alert-success').addClass('alert-danger').text('Please enter agent name.');
        return;
    }

    alertBox.addClass('d-none').text('');
    saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Saving...');

    $.ajax({
        url: "{{ url('agents/quick-add') }}",
        type: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            name: name,
            mobile: mobile
        },
        dataType: 'json',
        success: function(res) {
            saveBtn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Save & Select');
            if ((res.status === true || res.status === 'success' || res.status == 1) && res.data) {
                var agent = res.data;
                var displayText = agent.name + (agent.mobile ? ' (' + agent.mobile + ')' : '');
                
                // Select element update (works for standard select and select2)
                var agentSelect = $('select#agent_id, select[name="agent_id"]');
                if (agentSelect.length > 0) {
                    agentSelect.each(function() {
                        var $sel = $(this);
                        var opt = $sel.find('option[value="' + agent.id + '"]');
                        if (opt.length === 0) {
                            var newOption = new Option(displayText, agent.id, true, true);
                            $sel.append(newOption);
                        }
                        $sel.val(agent.id);
                        $sel.trigger('change');
                        if ($sel.hasClass('select2-hidden-accessible')) {
                            $sel.trigger('change.select2');
                        }
                    });
                }

                // Reset form & hide modal cleanly
                $('#quickAgentForm')[0].reset();
                $('#quickAgentModal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                if (typeof toastr !== 'undefined') {
                    toastr.success(res.message || 'Agent saved and selected successfully!');
                }
            } else {
                alertBox.removeClass('d-none alert-success').addClass('alert-danger').text(res.message || 'Failed to save agent.');
            }
        },
        error: function(xhr) {
            saveBtn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Save & Select');
            var errMsg = 'An error occurred while saving agent.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errMsg = xhr.responseJSON.message;
            }
            alertBox.removeClass('d-none alert-success').addClass('alert-danger').text(errMsg);
        }
    });
}
</script>
