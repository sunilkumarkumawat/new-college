@php
    $allBloodGroups = \Cache::remember('all_blood_groups_list', 120, function() {
        return DB::table('blood_groups')->whereNull('deleted_at')->pluck('name', 'id')->toArray();
    });
    $allGenders = \Cache::remember('all_genders_list', 120, function() {
        return DB::table('gender')->whereNull('deleted_at')->pluck('name', 'id')->toArray();
    });
    $roleId = Session::get('role_id');
@endphp

@if(!empty($data) && count($data) > 0)
    @foreach ($data as $item)
        @php
        //dd($item);
            $bgName = $allBloodGroups[$item->blood_group] ?? '-';
            $gName = $allGenders[$item->gender_id] ?? '-';
        @endphp
        <tr>
            <td class="text-center">
                <input type="checkbox" class="checkbox_id" 
                       data-admission_no="{{ $item['admissionNo'] ?? '' }}" 
                       data-name="{{ $item['first_name'] ?? '' }} {{ $item['last_name'] ?? '' }}" 
                       data-mobile="{{ $item['mobile'] ?? '' }}" 
                       data-father_name="{{ $item['father_name'] ?? '' }}" 
                       value="{{ $item->id }}">
            </td>                   
            <td class="text-center font-weight-bold text-muted">{{ $item->id }}</td>
            <td class="text-center font-weight-bold text-muted">{{ $item->admissionNo ?? '' }}</td>
          
            @if(!empty($dataTable))
                @foreach($dataTable as $val)
                    @if($val == 'Session' || $val == 'Class') @continue @endif
                    @if($val == 'Image')
                        <td class="text-center">
                            <img width="34px" height="34px" style="border-radius: 3px; object-fit: cover; border: 1px solid #cbd5e1; cursor: pointer;" 
                                 class="profileImg pointer deleteImage" 
                                 data-id="{{ $item->id ?? '' }}" 
                                 src="{{ env('IMAGE_SHOW_PATH').'profile/'.$item['image'] }}" 
                                 onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/user_image.jpg' }}'" 
                                 data-img="@if(!empty($item->image)) {{ env('IMAGE_SHOW_PATH').'profile/'.$item['image'] }} @endif">
                        </td>
                    @elseif($val == 'Name')
                        <td class="myBtn editables font-weight-bold" style="cursor: pointer; color: #002c54;" data-id="{{ $item->id ?? '' }}" data-field="first_name" data-modal="Admission">
                            {{ $item['first_name'] ?? '' }} {{ $item['last_name'] ?? '' }}
                        </td>
                    @elseif($val == 'D.O.B.')
                        <td>{{ !empty($item['dob']) ? date('d-m-Y', strtotime($item['dob'])) : '-' }}</td>
                    @elseif($val == 'State')
                        <td>{{ $item['State']['name'] ?? '-' }}</td>
                    @elseif($val == 'City')
                        <td>{{ $item['City']['name'] ?? '-' }}</td>
                    @elseif($val == 'Blood Group')
                        <td class="text-center">{{ $bgName }}</td>
                    @elseif($val == 'Gender')
                        <td class="text-center">{{ $gName }}</td>
                    @elseif($val == 'Admission Type')
                        <td class="text-center">
                            @if($item['admission_type_id'] == 1)
                                <span class="badge badge-light border">Non RTE</span>
                            @elseif($item['admission_type_id'] == 2)
                                <span class="badge badge-warning">RTE</span>
                            @else
                                -
                            @endif
                        </td>
                    @elseif($val == 'Ad. Date')
                        <td class="text-center">{{ !empty($item['admission_date']) ? date('d-m-Y', strtotime($item['admission_date'])) : '-' }}</td>
                    @elseif($val == 'Course')
                        <td class="text-center">
                            <div class="font-weight-bold" style="color: #002c54;">{{ $item['course'] ?? '-' }}</div>
                            @php
                                $currentClass = $item['ClassTypes']['name'] ?? ($item['class_name'] ?? '');
                            @endphp
                            @if(!empty($currentClass))
                                <small class="text-muted font-weight-normal d-block" style="font-size: 10.5px; line-height: 1.2;">{{ $currentClass }}</small>
                            @endif
                        </td>
                    @elseif($val == 'Batch')
                        <td class="text-center"><span class="badge badge-secondary" style="background:#475569;">{{ $item['batch'] ?? '-' }}</span></td>
                    @elseif($val == 'Student Type')
                        <td class="text-center">{{ $item['student_type'] ?? '-' }}</td>
                    @elseif($val == 'Mobile')
                        @if($roleId == 1)
                            <td class="text-center">{{ $item['mobile'] ?? '-' }}</td>
                        @endif
                    @elseif($val == 'Agent')
                        <td class="text-center">
                            @php
                                $agentName = $item->Agent->name ?? ($item['agent_name'] ?? '');
                            @endphp
                            @if(!empty($agentName))
                                <span class="badge badge-light border font-weight-bold" style="color: #166534; background: #f0fdf4;">{{ $agentName }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    @else
                        @php
                            $fieldKey = $getAdmissionDatatableFields[$val] ?? null;
                            $fieldVal = !empty($fieldKey) ? ($item[$fieldKey] ?? '') : '';
                        @endphp
                        <td class="text-center" data-id="{{ $item->id ?? '' }}" data-field="{{ $fieldKey }}" data-modal="Admission">
                            {{ $fieldVal !== '' ? $fieldVal : '-' }}
                        </td>
                    @endif
                @endforeach
            @endif
        
            <td class="fixed_item text-center"> 
                <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                    <a href="{{ url('studentDetail') }}/{{ $item['id'] ?? '' }}" class="btn-row-action btn-action-view" title="Admission View">
                        <i class="fa fa-eye"></i>
                    </a>
                    @if($item->status != 3)
                        <a href="{{ url('admissionStudentPrint') }}/{{ $item->id }}" target="_blank" class="btn-row-action btn-action-print" title="Admission Form">
                            <i class="fa fa-print"></i>
                        </a>
                        <a href="{{ url('admissionEdit') }}/{{ $item->id }}" target="_blank" class="btn-row-action btn-action-edit" title="Admission Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a href="{{ url('fees_assign') }}?student_lookup={{ $item->admissionNo ?? $item->id }}" target="_blank" class="btn-row-action btn-action-fees" title="View Fee Ledger & Assign Fees">
                            <i class="fa fa-money"></i>
                        </a>
                        <button type="button" data-id="{{ $item->id }}" data-bs-toggle="modal" data-bs-target="#Modal_id" data-toggle="modal" data-target="#Modal_id" class="btn-row-action btn-action-delete deleteData" title="Admission Delete">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    @else
                        <button type="button" class="btn-action-verify verify_admission" data-session_id="{{ $item->session_id }}" data-id="{{ $item->id }}" title="Verified Admission">
                            <i class="fa fa-check-circle mr-1"></i> Verify
                        </button>
                    @endif
                </div>
            </td>    
        </tr>
    @endforeach
@else
    <tr class="admission-empty-row">
        <td colspan="100" class="admission-empty-cell">
            <div class="empty-state-sticky-container">
                <div class="empty-state-wrap">
                    <div class="empty-state-icon-wrap">
                        <i class="fa fa-users"></i>
                    </div>
                    <div class="empty-state-title">
                        No Student Records Found
                    </div>
                    <p class="empty-state-subtitle">
                        No admissions match the selected filter criteria.
                    </p>
                    <button type="button" class="btn-empty-reset" onclick="$('#admissionResetBtn').trigger('click');">
                        <i class="fa fa-refresh"></i> Reset All Filters
                    </button>
                </div>
            </div>
        </td>
    </tr>
@endif
