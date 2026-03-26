<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('page_name')</title>

    <link rel="shortcut icon" href="{{ asset('images/icons/cogo-tab.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/light.css') }}">
    {{-- font CDN --}}
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(4px);
            z-index: 9999;
        }

        .loader-box {
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            text-align: center;
            min-width: 260px;
        }

        .loader-spinner {
            width: 3rem;
            height: 3rem;
        }
    </style>
    @stack('style')
</head>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <div class="wrapper">
        @include('layout.sidebar')
        <div class="main">

            @include('layout.navbar')
            @yield('content')

        </div>
    </div>
    @if (session('message'))
        @php
            $status = session('status') ?? 'success'; // Default to success
            $bgClass = $status === 'success' ? 'text-bg-success' : 'text-bg-danger';
        @endphp

        <div aria-live="polite" aria-atomic="true" class="position-relative" style="z-index: 1100;">
            <div class="toast-container position-fixed end-0 top-0 p-3">
                <div class="toast align-items-center {{ $bgClass }} show border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body text-white">
                            {{ session('message') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div id="pageLoader" class="page-loader">
        <div class="loader-box text-center">
            <div class="spinner-border text-primary loader-spinner"></div>
            <h6 class="mb-1 mt-3">Processing</h6>
            <p class="text-muted mb-0">Please wait while we process your data...</p>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/datatables.js') }}"></script>
    @stack('script')
    {{-- toast and form disable --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toastEl = document.getElementById('successToast');
            // var toast = new bootstrap.Toast(toastEl);
            // toast.show();
        });
    </script>
    {{-- disable button --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            document.querySelectorAll("form").forEach(function(form) {

                form.addEventListener("submit", function() {

                    const loader = document.getElementById("pageLoader");
                    if (loader) {
                        loader.style.display = "flex";
                    }

                    const btn = form.querySelector('button[type="submit"], input[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...';
                    }

                });

            });

        });
    </script>

    {{-- toast and form disable --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'))
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

        });
    </script>

    <script>
        function togglePassword(inputId, iconWrapper) {
            const input = document.getElementById(inputId);
            const icon = iconWrapper.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }

        function checkPasswordMatch() {
            const pass = document.getElementById('new_password').value;
            const conf = document.getElementById('confirm_password').value;
            const saveBtn = document.getElementById('addClientSave');

            const errorDiv = document.getElementById('passwordError');
            const rulesDiv = document.getElementById('passwordRulesError');

            // Password rules
            const hasUppercase = /[A-Z]/.test(pass);
            const hasNumber = /\d/.test(pass);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(pass);
            const hasMinLength = pass.length >= 8;

            let isValid = true; // ✅ DEFINE isValid

            /* Password rule validation */
            if (pass && (!hasUppercase || !hasNumber || !hasSpecial || !hasMinLength)) {
                rulesDiv.innerHTML =
                    'Password must contain:<br>' +
                    '• Minimum 8 characters<br>' +
                    '• One uppercase letter<br>' +
                    '• One number<br>' +
                    '• One special character';
                rulesDiv.style.display = 'block';
                isValid = false;
            } else {
                rulesDiv.style.display = 'none';
            }

            /* Password match validation */
            if (pass && conf && pass !== conf) {
                errorDiv.textContent = 'Passwords do not match!';
                errorDiv.style.display = 'block';
                isValid = false;
            } else {
                errorDiv.style.display = 'none';
            }

            /* Enable / Disable button */
            saveBtn.disabled = !isValid || !pass || !conf;

            return isValid;
        }
    </script>

</body>

</html>
