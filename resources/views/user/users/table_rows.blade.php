@php
$actionPermission = Helper::actionPermission();
$roleId = Session::get('role_id') ?? 1;
$canEdit = ($roleId == 1) || (!empty($actionPermission) && (is_array($actionPermission) ? ($actionPermission['edit'] ?? 0) : ($actionPermission->edit ?? 0)) == 1);
$canDelete = ($roleId == 1) || (!empty($actionPermission) && (is_array($actionPermission) ? ($actionPermission['deletes'] ?? 0) : ($actionPermission->deletes ?? 0)) == 1);
@endphp

@if(!empty($data) && count($data) > 0)
    @foreach ($data as $item)
        @php
            $fullName = trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? ''));
            $imgUrl = !empty($item->image) ? env('IMAGE_SHOW_PATH').'profile/'.$item->image : env('IMAGE_SHOW_PATH').'default/user_image.jpg';
            $safeItemJson = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
            $staffCode = !empty($item->UniqueId) ? $item->UniqueId : ('EMP-' . sprintf('%04d', $item->id));
        @endphp
        <tr id="row-user-{{ $item->id }}">
            {{-- Checkbox --}}
            <td class="text-center">
                <input type="checkbox" class="checkbox_id" 
                       data-id="{{ $item->id }}" 
                       data-name="{{ $fullName ?: ($item->userName ?? '') }}" 
                       data-mobile="{{ $item->mobile ?? '' }}" 
                       value="{{ $item->id }}">
            </td>

            {{-- SR.NO (Database ID) --}}
            <td class="text-center font-weight-bold text-muted">{{ $item->id }}</td>

            {{-- Image Thumbnail --}}
            <td class="text-center">
                <img width="32px" height="32px" 
                     style="border-radius: 50%; object-fit: cover; border: 1px solid #cbd5e1; cursor: pointer;" 
                     class="profileImg pointer view-avatar-preview" 
                     data-id="{{ $item->id }}" 
                     data-name="{{ $fullName ?: ($item->userName ?? 'User') }}"
                     src="{{ $imgUrl }}" 
                     onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/user_image.jpg' }}'" 
                     data-img="{{ $imgUrl }}">
            </td>

            {{-- Staff ID --}}
            <td>
                <span class="badge badge-light border font-weight-bold text-dark px-2 py-1">
                    {{ $staffCode }}
                </span>
            </td>

            {{-- Name & Username --}}
            <td>
                <div class="font-weight-bold text-dark" style="font-size: 11.5px;">
                    {{ $fullName ?: ($item->userName ?? 'N/A') }}
                </div>
                <div class="text-muted" style="font-size: 10px;">
                    <span>@</span>{{ $item->userName ?? '' }}
                    @if(!empty($item->gender))
                        <span class="badge badge-light border ml-1 px-1">{{ $item->gender }}</span>
                    @endif
                    @if(!empty($item->blood_group))
                        <span class="badge badge-light border text-danger px-1">{{ $item->blood_group }}</span>
                    @endif
                </div>
            </td>

            {{-- Role --}}
            <td>
                <span class="role-badge font-weight-bold">{{ $item->roleName->name ?? 'User' }}</span>
            </td>

            {{-- Department --}}
            <td>
                <span class="text-dark">{{ $item->department ?: 'General' }}</span>
            </td>

            {{-- Designation --}}
            <td>
                <span class="text-dark">{{ $item->designation ?: '-' }}</span>
            </td>

            {{-- Mobile --}}
            <td>
                @if(!empty($item->mobile))
                    <div>
                        <a href="tel:{{ $item->mobile }}" class="text-dark font-weight-bold" style="text-decoration: none;">
                            <i class="fa fa-phone text-success mr-1"></i>{{ $item->mobile }}
                        </a>
                        <a href="https://wa.me/91{{ $item->mobile }}" target="_blank" class="text-success ml-1" title="Chat on WhatsApp">
                            <i class="fa fa-whatsapp"></i>
                        </a>
                    </div>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>

            {{-- Email --}}
            <td>
                @if(!empty($item->email))
                    <a href="mailto:{{ $item->email }}" class="text-secondary" style="text-decoration: none; font-size: 11px;">
                        <i class="fa fa-envelope-o text-muted mr-1"></i>{{ $item->email }}
                    </a>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>

            {{-- Joining Date --}}
            <td class="text-center">
                {{ !empty($item->joining_date) ? date('d-m-Y', strtotime($item->joining_date)) : '-' }}
            </td>

            {{-- Status --}}
            <td class="text-center status-col-cell">
                @if($canEdit && $item->role_id != 1)
                    @if($item->status == 1)
                        <span class="badge-status-active btn-trigger-status" 
                              data-id="{{ $item->id }}" 
                              data-name="{{ $fullName ?: ($item->userName ?? 'User') }}" 
                              data-staff-id="{{ $staffCode }}"
                              data-role="{{ $item->roleName->name ?? 'User' }}"
                              data-dept="{{ $item->department ?: 'General' }}"
                              data-status="0" 
                              title="Click to Change Status">
                            <i class="fa fa-check"></i> Active
                        </span>
                    @else
                        <span class="badge-status-inactive btn-trigger-status" 
                              data-id="{{ $item->id }}" 
                              data-name="{{ $fullName ?: ($item->userName ?? 'User') }}" 
                              data-staff-id="{{ $staffCode }}"
                              data-role="{{ $item->roleName->name ?? 'User' }}"
                              data-dept="{{ $item->department ?: 'General' }}"
                              data-status="1" 
                              title="Click to Change Status">
                            <i class="fa fa-times"></i> Inactive
                        </span>
                    @endif
                @else
                    @if($item->status == 1)
                        <span class="badge-status-active"><i class="fa fa-check"></i> Active</span>
                    @else
                        <span class="badge-status-inactive"><i class="fa fa-times"></i> Inactive</span>
                    @endif
                @endif
            </td>

            {{-- Action (Fixed / Sticky Column) --}}
            <td class="fixed_item text-center">
                <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                    <!-- View Profile Modal -->
                    <button type="button" 
                            class="btn-row-action btn-action-view btn-view-user-detail" 
                            data-id="{{ $item->id }}" 
                            data-name="{{ $fullName ?: ($item->userName ?? 'Staff Member') }}" 
                            data-staff-id="{{ $staffCode }}" 
                            data-role="{{ $item->roleName->name ?? ($item->role_id == 1 ? 'Admin' : ($item->role_id == 2 ? 'Teacher' : 'Staff')) }}" 
                            data-dept="{{ $item->department ?: 'General' }}" 
                            data-username="{{ $item->userName ?? '' }}" 
                            data-mobile="{{ $item->mobile ?? '' }}" 
                            data-email="{{ $item->email ?? '' }}" 
                            data-status="{{ $item->status ?? 1 }}" 
                            data-user="{{ $safeItemJson }}" 
                            data-img="{{ $imgUrl }}" 
                            title="View Full Profile">
                        <i class="fa fa-eye"></i>
                    </button>

                    <!-- Edit -->
                    @if($canEdit)
                        <a href="{{ url('editUser', $item->id) }}" class="btn-row-action btn-action-edit" title="Edit User">
                            <i class="fa fa-pencil"></i>
                        </a>
                    @endif

                    <!-- Delete -->
                    @if($item->role_id !== 1 && $item->role_id !== $roleId)
                        @if($canDelete)
                            <button type="button" 
                                    class="btn-row-action btn-action-delete btn-trigger-delete" 
                                    data-id="{{ $item->id }}" 
                                    data-name="{{ $fullName ?: ($item->userName ?? '') }}"
                                    data-staff-id="{{ $staffCode }}"
                                    data-role="{{ $item->roleName->name ?? 'User' }}"
                                    data-dept="{{ $item->department ?: 'General' }}"
                                    title="Delete User">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        @endif
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
@else
    <tr class="admission-empty-row">
        <td colspan="100" class="admission-empty-cell">
            <div class="empty-state-wrap">
                <div class="empty-state-icon-wrap">
                    <i class="fa fa-users"></i>
                </div>
                <div class="empty-state-title">
                    No User Records Found
                </div>
                <p class="empty-state-subtitle">
                    No users match the selected search or filter criteria.
                </p>
                <button type="button" class="btn-empty-reset" onclick="$('#userResetBtn').trigger('click');">
                    <i class="fa fa-refresh"></i> Reset All Filters
                </button>
            </div>
        </td>
    </tr>
@endif
