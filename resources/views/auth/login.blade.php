@php
    $setting = DB::table('settings')->get()->first();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(!empty($setting))
        <title>{{ $setting->name ?? 'Shri Girraj Educational and Welfare Society' }}</title>
        <link rel="icon" type="image/x-icon" href="{{ env('IMAGE_SHOW_PATH').'setting/left_logo/'.$setting->left_logo ?? '' }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/mini_logo.png' }}'">
    @else
        <title>Shri Girraj Educational and Welfare Society</title>
    @endif

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Toastr & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="{{ asset('public/assets/school/css/toastr.min.css') }}">
    <script src="{{ asset('public/assets/school/js/toastr.min.js') }}"></script>

    <style>
        :root {
            --theme-green: #217346;
            --theme-green-dark: #165b35;
            --theme-green-darker: #0f4024;
            --theme-green-light: #f0fdf4;
            --theme-green-border: #86efac;
            --theme-text-dark: #0f291e;
            --slate-800: #1e293b;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
            --danger: #dc2626;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0e291d 0%, #17422b 50%, #0d281a 100%);
            position: relative;
            padding: 20px;
            color: var(--slate-800);
        }

        /* Subtle Background Texture Overlay */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(33, 115, 70, 0.25) 0%, transparent 45%),
                radial-gradient(circle at 80% 80%, rgba(33, 115, 70, 0.2) 0%, transparent 45%),
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 36px 36px, 36px 36px;
            pointer-events: none;
            z-index: 0;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
        }

        /* Clean Sober Card */
        .login-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid var(--theme-green);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            animation: fadeIn 0.35s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header Section */
        .login-header {
            padding: 32px 30px 20px 30px;
            text-align: center;
            background: #ffffff;
        }

        .logo-frame {
            width: 70px;
            height: 70px;
            margin: 0 auto 16px auto;
            border-radius: 14px;
            background: #ffffff;
            border: 1.5px solid var(--slate-200);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .logo-frame:hover {
            transform: scale(1.03);
            border-color: var(--theme-green-border);
        }

        .logo-frame img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .logo-frame i {
            font-size: 32px;
            color: var(--theme-green);
        }

        .college-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--slate-800);
            line-height: 1.35;
            margin-bottom: 6px;
        }

        .login-subtitle {
            font-size: 0.82rem;
            color: var(--slate-500);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--slate-50);
            padding: 4px 12px;
            border-radius: 20px;
            border: 1px solid var(--slate-200);
        }

        .login-subtitle i {
            color: var(--theme-green);
            font-size: 0.75rem;
        }

        /* Body & Form */
        .login-body {
            padding: 10px 30px 28px 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--slate-600);
            margin-bottom: 6px;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--slate-400);
            font-size: 0.9rem;
            transition: color 0.2s ease;
            pointer-events: none;
            z-index: 2;
        }

        .form-control-custom {
            width: 100%;
            height: 44px;
            padding: 10px 42px 10px 40px;
            font-size: 0.92rem;
            font-family: inherit;
            color: var(--slate-800);
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-control-custom::placeholder {
            color: #94a3b8;
            font-size: 0.86rem;
        }

        .form-control-custom:focus {
            background: #ffffff;
            border-color: var(--theme-green);
            box-shadow: 0 0 0 3px rgba(33, 115, 70, 0.15);
        }

        .form-control-custom:focus + .input-icon-left,
        .input-group-custom:focus-within .input-icon-left {
            color: var(--theme-green);
        }

        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--slate-400);
            font-size: 0.92rem;
            padding: 6px;
            cursor: pointer;
            border-radius: 4px;
            transition: color 0.2s ease;
            z-index: 2;
        }

        .password-toggle-btn:hover {
            color: var(--theme-green);
        }

        .field-error {
            display: block;
            font-size: 0.76rem;
            color: var(--danger);
            margin-top: 5px;
            font-weight: 500;
            min-height: 1.1em;
        }

        /* Options Row */
        .login-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            font-size: 0.82rem;
        }

        .remember-checkbox {
            display: flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            color: var(--slate-600);
            user-select: none;
        }

        .remember-checkbox input[type="checkbox"] {
            accent-color: var(--theme-green);
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        .forgot-link {
            color: var(--theme-green);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .forgot-link:hover {
            color: var(--theme-green-darker);
            text-decoration: underline;
        }

        /* Submit Button */
        .btn-theme-submit {
            width: 100%;
            height: 44px;
            background: var(--theme-green);
            color: #ffffff;
            border: 1px solid var(--theme-green-dark);
            border-radius: 8px;
            font-size: 0.94rem;
            font-weight: 600;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(33, 115, 70, 0.25);
            transition: all 0.2s ease;
        }

        .btn-theme-submit:hover:not(:disabled) {
            background: var(--theme-green-dark);
            box-shadow: 0 4px 10px rgba(33, 115, 70, 0.35);
            transform: translateY(-1px);
        }

        .btn-theme-submit:active:not(:disabled) {
            background: var(--theme-green-darker);
            transform: translateY(0);
        }

        .btn-theme-submit:disabled {
            opacity: 0.75;
            cursor: not-allowed;
            transform: none;
        }

        /* Footer inside card */
        .card-footer-custom {
            padding: 16px 30px;
            background: var(--slate-50);
            border-top: 1px solid var(--slate-100);
            text-align: center;
            font-size: 0.75rem;
            color: var(--slate-500);
        }

        .card-footer-custom b {
            color: var(--slate-800);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                border-radius: 10px;
            }
            .login-header {
                padding: 24px 20px 16px 20px;
            }
            .login-body {
                padding: 10px 20px 22px 20px;
            }
            .college-title {
                font-size: 1.05rem;
            }
            .logo-frame {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <!-- Header -->
        <div class="login-header">
            <div class="logo-frame">
                @if(!empty($setting) && !empty($setting->left_logo))
                    <img src="{{ env('IMAGE_SHOW_PATH').'setting/left_logo/'.$setting->left_logo }}" 
                         alt="College Logo" 
                         onerror="this.style.display='none';this.parentElement.innerHTML='<i class=\'fa-solid fa-graduation-cap\'></i>'">
                @else
                    <i class="fa-solid fa-graduation-cap"></i>
                @endif
            </div>

            <h1 class="college-title">
                {{ $setting->name ?? 'Shri Girraj Educational and Welfare Society' }}
            </h1>
            <div class="login-subtitle">
                <i class="fa-solid fa-shield-halved"></i> Secure ERP &amp; Academic Portal
            </div>
        </div>

        <!-- Body Form -->
        <div class="login-body">
            @include('layout.message')

            <form id="loginForm" action="{{ url('login') }}" method="POST">
                @csrf

                <!-- Username -->
                <div class="form-group">
                    <label for="user_name" class="form-label">Username</label>
                    <div class="input-group-custom">
                        <i class="fa-solid fa-user input-icon-left"></i>
                        <input type="text" 
                               id="user_name" 
                               name="user_name" 
                               class="form-control-custom @error('user_name') is-invalid @enderror" 
                               placeholder="Enter your username" 
                               value="{{ old('user_name') }}" 
                               autocomplete="username" 
                               required>
                    </div>
                    <span class="field-error" id="user_name_error"></span>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group-custom">
                        <i class="fa-solid fa-lock input-icon-left"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control-custom @error('password') is-invalid @enderror" 
                               placeholder="Enter your password" 
                               autocomplete="current-password" 
                               required>
                        <button type="button" class="password-toggle-btn" id="togglePasswordBtn" title="Show / Hide Password">
                            <i class="fa-solid fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    <span class="field-error" id="password_error"></span>
                </div>

                <!-- Options -->
                <div class="login-options">
                    <label class="remember-checkbox">
                        <input type="checkbox" name="remember" id="rememberMe">
                        <span>Remember me</span>
                    </label>
                    <a href="{{ url('forgot_password') }}" class="forgot-link">
                        Forgot Password?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-theme-submit" id="submitBtn">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Sign In</span>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="card-footer-custom">
            Powered by <b>Rukmani Software</b> · Jaipur
        </div>
    </div>
</div>

<script>
    // Password Toggle Functionality
    $(document).ready(function () {
        $('#togglePasswordBtn').on('click', function () {
            var passwordInput = $('#password');
            var icon = $('#toggleIcon');

            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // AJAX Form Submission
        $('#loginForm').on('submit', function (e) {
            e.preventDefault();

            var form = $(this);
            var submitButton = $('#submitBtn');

            // Set loading state
            submitButton.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Signing in…</span>');
            $('#user_name_error').text('');
            $('#password_error').text('');

            var data = form.serialize();

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response.status === 'success') {
                        submitButton.html('<i class="fa-solid fa-check"></i> <span>Success! Redirecting…</span>');
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Login successful!');
                        }
                        setTimeout(function () {
                            window.location.href = response.redirect_url;
                        }, 500);
                    } else {
                        submitButton.prop('disabled', false).html('<i class="fa-solid fa-right-to-bracket"></i> <span>Sign In</span>');
                        $('#user_name_error').text(response.user_name_error || '');
                        $('#password_error').text(response.password_error || '');
                        if (response.message && response.message !== "") {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message);
                            }
                        }
                    }
                },
                error: function (xhr) {
                    submitButton.prop('disabled', false).html('<i class="fa-solid fa-right-to-bracket"></i> <span>Sign In</span>');
                    var errors = xhr.responseJSON ? xhr.responseJSON.errors : {};
                    $('#user_name_error').text(errors && errors.user_name ? errors.user_name[0] : '');
                    $('#password_error').text(errors && errors.password ? errors.password[0] : '');

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(xhr.responseJSON.message);
                        }
                    }
                }
            });
        });
    });
</script>

</body>
</html>
