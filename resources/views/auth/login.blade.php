<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Sign In</title>

    @include('layout.styles')
    <link rel="shortcut icon" href="{{ asset('images/icons/cogo-tab.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.1.0/css/all.min.css">

    <style>
        /* Background Styling with Overlay */
        .bg-img {
            /* The linear-gradient creates the dark opacity overlay over the image */
            background-image: linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.45)), url('{{ asset('images/bg-banner.jpeg') }}'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* Glassmorphism Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.15); /* Semi-transparent white */
            backdrop-filter: blur(12px); /* The blur effect */
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2); /* Subtle white border */
            border-radius: 20px;
            color: #ffffff; /* White text */
            padding: 40px 30px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }

        /* Input Labels */
        .glass-label {
            font-size: 12px;
            font-weight: 500;
            color: #ffffff;
            margin-bottom: 8px;
        }

        /* Glass Inputs */
        .glass-input {
            background: transparent !important;
            border: 1px solid rgba(255, 255, 255, 0.4) !important;
            border-radius: 25px !important;
            color: #ffffff !important;
            padding-left: 45px !important; /* Space for the left icon */
            height: 45px;
        }

        .glass-input::placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
            font-size: 13px;
        }

        .glass-input:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: #ffffff !important;
            box-shadow: none !important;
        }

        /* Icons inside inputs */
        .input-icon-left {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            pointer-events: none; /* Prevents icon from blocking clicks */
        }

        .input-icon-right {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            cursor: pointer;
        }

        /* Green Button */
        .btn-green {
            background-color: #5aa715; /* Vibrant green from image */
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            height: 45px;
            transition: all 0.3s ease;
        }

        .btn-green:hover {
            background-color: #4a8c11;
            color: white;
        }

        /* Footer Links */
        .footer-text {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
        }

        .forgot-link {
            color: #ffffff;
            font-size: 12px;
            font-weight: 600;
            text-decoration: underline;
        }

        .forgot-link:hover {
            color: #e0e0e0;
        }

        /* Logo Placeholder Styling (Remove if using an actual img tag) */
        .logo-text {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .logo-cogo { color: #00c853; }
        .logo-smart { color: #333; } /* Or white depending on your actual logo */
    </style>
</head>

<body class="reg-bg bg-img">
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                
                <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                    
                    <div class="glass-card">
                        
                        <div class="text-center mb-4">
                            <img src="{{ asset('images/icons/cogosmart-logo.png') }}" alt="" height="30px">
                            <h3 class="fw-bold text-white mt-2">Login</h3>
                        </div>

                        <form action="{{ route('admin.login.post') }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="glass-label">Enter your Mobile Number</label>
                                <div class="position-relative">
                                    <i class="fas fa-phone-alt input-icon-left"></i>
                                    <input type="text" class="form-control glass-input" name="phone" placeholder="8438298692" value="{{ old('phone') }}" maxlength="10" minlength="10" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="glass-label">Enter your Password</label>
                                <div class="position-relative">
                                    <i class="fas fa-lock input-icon-left"></i>
                                    <input type="password" class="form-control glass-input" name="password" id="password" placeholder="**********" required>
                                    <span class="input-icon-right" onclick="togglePassword('password')">
                                        <i class="fas fa-eye-slash" id="togglePasswordIcon"></i>
                                    </span>
                                </div>
                                <div id="passwordError" class="text-danger mt-1" style="display: none;"></div>
                            </div>

                            <button type="submit" class="btn btn-green w-100 mt-2 shadow-sm">Login</button>

                            <!-- <div class="d-flex justify-content-between align-items-center mt-4">
                                <span class="footer-text">Did you forget your password?</span>
                                <a href="#" class="forgot-link">Forgot Password</a>
                            </div> -->

                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    @if (session('login_error') || session('message'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div class="toast show align-items-center text-white border-0 shadow {{ session('login_error') ? 'bg-danger' : 'bg-success' }}">
            <div class="d-flex">
                <div class="toast-body fw-medium">
                    {{ session('login_error') ?? session('message') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div> @endif

    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        // Toggle Password Visibility
        function togglePassword(fieldId) {
            const input = document.getElementById(fieldId);
            const icon = document.getElementById('togglePasswordIcon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>
