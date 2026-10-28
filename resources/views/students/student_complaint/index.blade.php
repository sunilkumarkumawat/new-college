@php
$statusClass = ['pending' => 'warning', 'in_progress' => 'info', 'resolved' => 'success'];
@endphp
@extends('layout.app')
@section('content')

<style>
    .sc-wrap {
        white-space: normal !important;
        word-break: break-word;
        min-width: 120px;
    }
    .sc-reply {
        font-size: 12px;
        color: #555;
        margin-top: 4px;
    }
</style>

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <div class="row">
                <!-- Add Complaint -->
                <div class="col-md-4">
                    <div class="card card-outline card-orange">
                        <div class="card-header bg-primary">
                            <h3 class="card-title"><i class="fa fa-plus"></i> &nbsp;Add Complaint</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ url('student_complaints') }}" method="post">
                                @csrf
                                <div class="form-group">
                                    <label>{{ __('common.Subject') }} <span class="text-danger">*</span></label>
                                    <input class="form-control @error('subject') is-invalid @enderror" type="text" name="subject" value="{{ old('subject') }}" maxlength="255" placeholder="{{ __('common.Subject') }}">
                                    @error('subject')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('dashboard.Description') }} <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="5" placeholder="{{ __('dashboard.Description') }}">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">{{ __('common.submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- My Complaints -->
                <div class="col-md-8">
                    <div class="card card-outline card-orange">
                        <div class="card-header bg-primary">
                            <h3 class="card-title"><i class="fa fa-list-alt"></i> &nbsp;My Complaints</h3>
                            <div class="card-tools">
                                <a href="{{ url('dashboard') }}" class="btn btn-sm btn-light"><i class="fa fa-arrow-left"></i> {{ __('common.Back') }}</a>
                            </div>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('common.SR.NO') }}</th>
                                        <th>{{ __('common.Subject') }}</th>
                                        <th>{{ __('dashboard.Description') }}</th>
                                        <th>{{ __('common.Date') }}</th>
                                        <th>Status / Reply</th>
                                        <th>{{ __('common.Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $key => $item)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td class="sc-wrap">{{ $item->subject }}</td>
                                            <td class="sc-wrap">{{ $item->description }}</td>
                                            <td>{{ date('d-m-Y', strtotime($item->date)) }}</td>
                                            <td class="sc-wrap">
                                                <span class="badge badge-{{ $statusClass[$item->status] ?? 'secondary' }} bg-{{ $statusClass[$item->status] ?? 'secondary' }}">{{ \App\Models\StudentComplaint::STATUSES[$item->status] ?? $item->status }}</span>
                                                @if($item->admin_reply)
                                                    <div class="sc-reply"><b>Admin:</b> {{ $item->admin_reply }}</div>
                                                @endif
                                            </td>
                                            <td style="white-space: nowrap;">
                                                @if($item->canStudentEdit())
                                                    <a href="javascript:;" class="editBtn btn btn-primary btn-xs" title="Edit Complaint"
                                                        data-id="{{ $item->id }}"
                                                        data-subject="{{ $item->subject }}"
                                                        data-description="{{ $item->description }}"><i class="fa fa-edit"></i></a>
                                                    <a href="javascript:;" data-id="{{ $item->id }}" class="deleteBtn btn btn-danger btn-xs" title="Delete Complaint"><i class="fa fa-trash-o"></i></a>
                                                @else
                                                    <span class="text-muted" title="Admin has replied"><i class="fa fa-lock"></i></span>
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

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Complaint</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ url('student_complaints_update') }}" method="post">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="edit_complaint_id" name="complaint_id">
                    <div class="form-group">
                        <label>{{ __('common.Subject') }} <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" id="edit_subject" name="subject" maxlength="255" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __('dashboard.Description') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_description" name="description" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">{{ __('common.Close') }}</button>
                    <button type="submit" class="btn btn-primary">Update</button>
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
    $(document).on('click', '.editBtn', function() {
        $('#edit_complaint_id').val($(this).data('id'));
        $('#edit_subject').val($(this).attr('data-subject'));
        $('#edit_description').val($(this).attr('data-description'));
        $('#editModal').modal('show');
    });

    $(document).on('click', '.deleteBtn', function() {
        $('#delete_id').val($(this).data('id'));
        $('#deleteModal').modal('show');
    });
</script>
@endsection