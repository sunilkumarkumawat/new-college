@php
    $getAttendanceStatus = Helper::getAttendanceStatus();
@endphp

@if(!empty($data) && $data->count() > 0)
    @php $i = 1; @endphp
    @foreach ($data as $key => $item)
        @php
            $currentStatus = $attendances[$item->id] ?? null;
            $rowClass = '';
            if ($currentStatus == 1) $rowClass = 'row-status-present';
            elseif ($currentStatus == 2) $rowClass = 'row-status-absent';
            elseif ($currentStatus == 3) $rowClass = 'row-status-leave';
            elseif ($currentStatus == 4) $rowClass = 'row-status-halfday';
            elseif ($currentStatus == 5) $rowClass = 'row-status-holiday';

            $studentName = trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''));
        @endphp
        <tr class="attendance-row {{ $rowClass }}" data-student-id="{{ $item['id'] }}">
            <td class="text-center">
                <input type="checkbox" class="checkbox checkbox_select row-checkbox" 
                    name="studentId[]" 
                    value="{{ $item['id'] }}" 
                    data-admission_no="{{ $item['admissionNo'] ?? '' }}" 
                    data-student_name="{{ $studentName }}"
                    checked>
            </td>
            <td class="text-center font-weight-bold text-muted">{{ $i++ }}</td>
            <td class="text-center">
                <span class="adm-badge">
                    {{ $item['admissionNo'] ?? '-' }}
                </span>
            </td>
            <td>
                <div class="student-name-cell">
                    <span class="font-weight-bold text-dark">{{ $studentName ?: '-' }}</span>
                    @if(!empty($item['mobile']))
                        <small class="text-muted ml-1"><i class="fa fa-phone text-secondary"></i> {{ $item['mobile'] }}</small>
                    @endif
                </div>
            </td>
            <td>
                <span class="text-dark font-weight-500">{{ $item['ClassTypes']['name'] ?? '-' }}</span>
                @if (!empty($item['Section']['name']))
                    <span class="text-muted small">({{ $item['Section']['name'] }})</span>
                @endif
            </td>
            <td>
                <span class="text-dark">{{ $item['father_name'] ?? '-' }}</span>
            </td>
            <td class="text-center" style="white-space: nowrap;">
                <span class="text-muted small">{{ isset($item['dob']) ? \Carbon\Carbon::parse($item['dob'])->format('d-m-Y') : '-' }}</span>
            </td>
            <td class="text-center" style="padding: 2px 4px !important;">
                <!-- Hidden inputs preserving backend contract -->
                <input type="hidden" name="admission_id[]" value="{{ $item['id'] }}">
                <input type="hidden" name="class_type_id[]" value="{{ $item['class_type_id'] ?? '' }}">
                <input type="hidden" name="name[]" value="{{ $studentName }}">
                <input type="hidden" name="first_name[]" value="{{ $item['first_name'] ?? '' }}">
                <input type="hidden" name="email[]" value="{{ $item['email'] ?? '' }}">
                <input type="hidden" name="mobile[]" value="{{ $item['mobile'] ?? '' }}">
                <input type="hidden" id="time_{{ $item['id'] }}" value="{{ date('H:i:s') }}">
                
                <input type="hidden" 
                    class="attendance_status_input attendance_status_{{ $item['id'] }}" 
                    name="attendance_status[]" 
                    id="attendance_status_{{ $item['id'] }}"
                    value="{{ $currentStatus ?? '' }}"
                    data-name="{{ $studentName }}"
                    data-mobile="{{ $item['mobile'] ?? '' }}"
                    data-email="{{ $item['email'] ?? '' }}"
                    data-class_type_id="{{ $item['class_type_id'] ?? '' }}"
                    data-admission_id="{{ $item['id'] }}">

                <!-- Status Pill Action Buttons -->
                <div class="status-btn-group">
                    @if(!empty($getAttendanceStatus))
                        @foreach($getAttendanceStatus as $status)
                            @php
                                $statusId = (string) $status->id;
                                $statusName = $status->name ?? '';
                                $shortName = strtoupper(substr($statusName, 0, 1));
                                $isActive = ($currentStatus !== null && (string)$currentStatus === $statusId);
                                
                                $btnClass = 'btn-att-default';
                                if ($statusId == '1') { $btnClass = 'btn-att-p'; $shortName = 'P'; }
                                elseif ($statusId == '2') { $btnClass = 'btn-att-a'; $shortName = 'A'; }
                                elseif ($statusId == '3') { $btnClass = 'btn-att-l'; $shortName = 'L'; }
                                elseif ($statusId == '4') { $btnClass = 'btn-att-h'; $shortName = 'H'; }
                                elseif ($statusId == '5') { $btnClass = 'btn-att-hd'; $shortName = 'HD'; }
                            @endphp
                            <button type="button" 
                                class="btn-att-pill {{ $btnClass }} {{ $isActive ? 'active' : '' }}" 
                                data-id="{{ $item['id'] }}" 
                                data-status-id="{{ $statusId }}"
                                title="{{ $statusName }}">
                                {{ $shortName }}
                            </button>
                        @endforeach
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="8" class="empty-state-cell">
            <div class="empty-state-wrapper">
                <i class="fa fa-info-circle empty-state-icon"></i>
                <div class="empty-state-title">No Students Found</div>
                <div class="empty-state-desc">No students found matching the selected criteria.</div>
            </div>
        </td>
    </tr>
@endif