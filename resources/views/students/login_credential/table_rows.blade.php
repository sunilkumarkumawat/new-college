@php
    $currentPage = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $data->currentPage() : 1;
    $perPage = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $data->perPage() : count($data);
    $offset = ($currentPage - 1) * $perPage;
    $portalUrl = url('/');
@endphp

@if(!empty($data) && count($data) > 0)
    @foreach($data as $item)
        @php
            $hasCredentials = !empty($item->userName);
            $plainPassword = $item->confirm_password ?? ($item->Password ?? ($item->password ?? ''));
            $cleanMobile = preg_replace('/\D/', '', $item->mobile ?? '');
            $fullName = trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? ''));
            
            // Build WhatsApp share message
            $waMessage = "Dear *" . ($fullName ?: 'Student') . "*,\n\n"
                       . "Your ERP Student Portal Login Credentials are as follows:\n"
                       . "🌐 *Portal URL:* " . $portalUrl . "\n"
                       . "👤 *Username:* *" . ($item->userName ?: '-') . "*\n"
                       . "🔑 *Password:* *" . ($plainPassword ?: '-') . "*\n"
                       . "🎓 *Course:* " . ($item->course ?: '-') . " (" . ($item->batch ?: '-') . ")\n"
                       . "🏫 *Class:* " . ($item->class_name ?: '-') . "\n\n"
                       . "Please login and keep your credentials secure.\n"
                       . "Regards,\n" . (Session::get('school_name') ?? 'Girraj ERP System');
            $waUrl = !empty($cleanMobile) ? "https://api.whatsapp.com/send?phone=91" . substr($cleanMobile, -10) . "&text=" . urlencode($waMessage) : "#";
        @endphp
        <tr id="student_row_{{ $item->id }}">
            <td class="text-center">
                <input type="checkbox" class="student_checkbox pointer" 
                       value="{{ $item->id }}" 
                       data-id="{{ $item->id }}"
                       data-name="{{ $fullName }}"
                       data-adm="{{ $item->admissionNo ?? '' }}"
                       data-mobile="{{ $item->mobile ?? '' }}"
                       data-course="{{ $item->course ?? '' }}"
                       data-batch="{{ $item->batch ?? '' }}"
                       data-class="{{ $item->class_name ?? '' }}"
                       data-username="{{ $item->userName ?? '' }}"
                       data-password="{{ $plainPassword }}"
                       data-dob="{{ $item->dob ?? '' }}">
            </td>
            <td class="text-center text-muted font-weight-bold">{{ $offset + $loop->iteration }}</td>
            <td>
                <span class="badge badge-adm-no font-weight-bold">{{ $item->admissionNo ?? '-' }}</span>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="user-avatar mr-2">
                        {{ strtoupper(substr($item->first_name ?? 'S', 0, 1)) }}
                    </div>
                    <div>
                        <strong class="text-dark d-block text-truncate" style="max-width: 180px;">{{ $fullName }}</strong>
                        @if(!empty($item->father_name))
                            <small class="text-muted"><i class="fa fa-user-o mr-1"></i>S/D/O {{ $item->father_name }}</small>
                        @endif
                    </div>
                </div>
            </td>
            <td>
                @if(!empty($item->mobile))
                    <span class="text-dark font-weight-600"><i class="fa fa-phone text-muted mr-1"></i>{{ $item->mobile }}</span>
                @else
                    <span class="text-muted font-italic">-</span>
                @endif
            </td>
            <td>
                <span class="badge badge-course">{{ $item->course ?? '-' }}</span>
            </td>
            <td>
                <span class="badge badge-batch">{{ $item->batch ?? '-' }}</span>
            </td>
            <td>
                <strong class="text-dark">{{ $item->class_name ?? '-' }}</strong>
            </td>
            
            <!-- USERNAME COLUMN -->
            <td id="display_username_{{ $item->id }}">
                @if($hasCredentials)
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="credential-code">{{ $item->userName }}</span>
                        <button type="button" class="btn-cred-copy ml-1" title="Copy Username" onclick="copyToClipboard('{{ $item->userName }}', 'Username copied!')">
                            <i class="fa fa-clone"></i>
                        </button>
                    </div>
                @else
                    <span class="badge badge-status-missing" style="font-size: 10px;">
                        <i class="fa fa-exclamation-circle mr-1"></i> Not Set
                    </span>
                @endif
            </td>

            <!-- PASSWORD COLUMN -->
            <td id="display_password_container_{{ $item->id }}">
                @if(!empty($plainPassword))
                    <div class="password-field-wrapper d-flex align-items-center justify-content-between">
                        <span class="credential-code masked-pass" id="pass_masked_{{ $item->id }}">••••••••</span>
                        <span class="credential-code d-none plain-pass" id="pass_plain_{{ $item->id }}">{{ $plainPassword }}</span>
                        <div class="ml-1 d-inline-flex align-items-center">
                            <button type="button" class="btn-cred-toggle mr-1" title="Show / Hide Password" onclick="togglePasswordVisibility({{ $item->id }})">
                                <i class="fa fa-eye" id="pass_eye_icon_{{ $item->id }}"></i>
                            </button>
                            <button type="button" class="btn-cred-copy" title="Copy Password" onclick="copyToClipboard('{{ $plainPassword }}', 'Password copied!')">
                                <i class="fa fa-clone"></i>
                            </button>
                        </div>
                    </div>
                @else
                    <span class="text-muted font-italic">-</span>
                @endif
            </td>

            <!-- STATUS BADGE -->
            <td class="text-center" id="status_badge_{{ $item->id }}">
                @if($hasCredentials)
                    <span class="badge badge-status-active"><i class="fa fa-check-circle mr-1"></i> Active</span>
                @else
                    <span class="badge badge-status-pending"><i class="fa fa-clock-o mr-1"></i> Pending</span>
                @endif
            </td>

            <!-- ACTION BUTTONS -->
            <td class="text-center">
                <div class="d-inline-flex align-items-center" style="gap: 3px;">
                    <!-- Edit Single Credential Button -->
                    <button type="button" 
                            class="btn-row-action btn-action-edit edit-credential-btn" 
                            data-id="{{ $item->id }}"
                            data-name="{{ $fullName }}"
                            data-adm="{{ $item->admissionNo ?? '' }}"
                            data-course="{{ $item->course ?? '' }}"
                            data-batch="{{ $item->batch ?? '' }}"
                            data-class="{{ $item->class_name ?? '' }}"
                            data-username="{{ $item->userName ?? '' }}"
                            data-password="{{ $plainPassword }}"
                            data-mobile="{{ $item->mobile ?? '' }}"
                            data-dob="{{ $item->dob ?? '' }}"
                            title="Edit / Set Login Credentials">
                        <i class="fa fa-pencil"></i>
                    </button>

                    <!-- WhatsApp Share Button -->
                    @if(!empty($cleanMobile))
                        <a href="{{ $waUrl }}" 
                           target="_blank" 
                           class="btn-row-action btn-action-whatsapp" 
                           title="Send Login Details via WhatsApp">
                            <i class="fa fa-whatsapp"></i>
                        </a>
                    @else
                        <button type="button" class="btn-row-action btn-action-whatsapp opacity-50" disabled title="No Mobile Number">
                            <i class="fa fa-whatsapp"></i>
                        </button>
                    @endif

                    <!-- Copy All Info Button -->
                    @if($hasCredentials && !empty($plainPassword))
                        @php
                            $fullCopyText = "Student: {$fullName} | Adm: " . ($item->admissionNo ?: '-') . " | User: {$item->userName} | Pass: {$plainPassword} | URL: {$portalUrl}";
                        @endphp
                        <button type="button" 
                                class="btn-row-action btn-action-copy" 
                                onclick="copyToClipboard('{{ addslashes($fullCopyText) }}', 'All login details copied!')" 
                                title="Copy Complete Login Details">
                            <i class="fa fa-files-o"></i>
                        </button>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="12" class="text-center text-muted py-4">
            <div class="empty-state-box py-3">
                <i class="fa fa-user-times fa-3x mb-2 text-muted" style="opacity: 0.5;"></i>
                <h6 class="font-weight-bold text-secondary mb-1">No Student Credentials Found</h6>
                <p class="small text-muted mb-0">No records match your selected filters. Try changing or resetting the filters.</p>
            </div>
        </td>
    </tr>
@endif
