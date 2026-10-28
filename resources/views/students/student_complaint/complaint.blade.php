@php
$statuses = \App\Models\StudentComplaint::STATUSES;
$statusClass = ['pending' => 'warning', 'in_progress' => 'info', 'resolved' => 'success'];
@endphp
@extends('layout.app')
@section('content')

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-orange">
                        <div class="card-header bg-primary">
                            <h3 class="card-title"><i class="fa fa-list-alt"></i> &nbsp;Student Complaints</h3>
                            <div class="card-tools">
                                <a href="{{ url('dashboard') }}" class="btn btn-sm btn-light"><i class="fa fa-arrow-left"></i> {{ __('common.Back') }}</a>
                            </div>
                        </div>

                        <div class="card-body">
                            <form method="get" action="{{ url('student_complaints') }}" class="row mb-3">
                                <div class="col-md-3">
                                    <select name="status" class="form-control" onchange="this.form.submit()">
                                        <option value="">All Status</option>
                                        @foreach($statuses as $key => $label)
                                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>

                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('common.SR.NO') }}</th>
                                        <th>{{ __('common.Name') }}</th>
                                        <th>{{ __('common.Mobile No.') }}</th>
                                        <th>{{ __('common.Subject') }}</th>
                                        <th>{{ __('dashboard.Description') }}</th>
                                        <th>{{ __('common.Date') }}</th>
                                        <th>Status</th>
                                        <th>Admin Reply</th>
                                        <th>{{ __('common.Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item['Admission']['first_name'] ?? '' }} {{ $item['Admission']['last_name'] ?? '' }} ({{ $item['ClassType']['name'] ?? '' }})</td>
                                        <td>{{ $item['Admission']['mobile'] ?? '' }}</td>
                                        <td style="white-space: normal; word-break: break-word;">{{ $item->subject }}</td>
                                        <td style="white-space: normal; word-break: break-word; min-width: 180px;">{{ $item->description }}</td>
                                        <td>{{ date('d-m-Y', strtotime($item->date)) }}</td>
                                        <td><span class="badge badge-{{ $statusClass[$item->status] ?? 'secondary' }} bg-{{ $statusClass[$item->status] ?? 'secondary' }}">{{ $statuses[$item->status] ?? $item->status }}</span></td>
                                        <td style="white-space: normal; word-break: break-word; min-width: 150px;">{{ $item->admin_reply }}</td>
                                        <td>
                                            <button type="button" class="btn btn-warning btn-xs replyBtn" title="Reply / Update Status"
                                                data-id="{{ $item->id }}"
                                                data-status="{{ $item->status }}"
                                                data-reply="{{ $item->admin_reply }}"><i class="fa fa-envelope" style="color:#fff;"></i></button>
                                            @if(Session::get('role_id') == 1)
                                                <a href="javascript:;" data-id="{{ $item->id }}" class="deleteBtn btn btn-danger btn-xs" title="Delete Complaint"><i class="fa fa-trash-o"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Reply Modal -->
<div class="modal" id="replyModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Reply / Update Status</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ url('student_complaints_reply') }}" method="post">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="complaint_id" name="complaint_id">
                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="reply_status" name="status" required>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Reply</label>
                        <textarea class="form-control" id="admin_reply" name="admin_reply" rows="3" placeholder="Reply to student"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">{{ __('common.Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('common.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-dialog">
        <div class="modal-content" style="background: #555b5beb;">
            <div class="modal-header">
                <h4 class="modal-title text-white">{{ __('common.Delete Confirmation') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal"><i class="fa fa-times" aria-hidden="true"></i></button>
            </div>
            <form action="{{ url('student_complaints_delete') }}" method="post">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="delete_id" name="delete_id">
                    <h5 class="text-white">{{ __('common.Are you sure you want to delete') }} ?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal" data-dismiss="modal">{{ __('common.Close') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('common.Delete') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.replyBtn', function() {
        $('#complaint_id').val($(this).data('id'));
        $('#reply_status').val($(this).data('status'));
        $('#admin_reply').val($(this).attr('data-reply'));
        $('#replyModal').modal('show');
    });

    $(document).on('click', '.deleteBtn', function() {
        $('#delete_id').val($(this).data('id'));
        $('#deleteModal').modal('show');
    });
</script>
@endsection
