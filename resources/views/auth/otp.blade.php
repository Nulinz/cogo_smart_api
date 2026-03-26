<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Forgot Password</title>

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
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            color: #ffffff;
            padding: 40px 30px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }

        /* Input Labels */
        .glass-label {
            font-size: 13px;
            font-weight: 400;
            color: #ffffff;
            margin-bottom: 8px;
            display: block;
        }

        /* Glass Inputs */
        .glass-input {
            background: transparent !important;
            border: 1px solid rgba(255, 255, 255, 0.4) !important;
            border-radius: 25px !important;
            color: #ffffff !important;
            padding-left: 45px !important;
            height: 50px;
        }

        /* Extra padding on the right so text doesn't hide behind the Send OTP button */
        .glass-input-mobile {
            padding-right: 110px !important;
        }

        .glass-input::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
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
            pointer-events: none;
        }

        /* Send OTP Button (Inline) */
        .btn-send-otp {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            background-color: #5aa715;
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 18px;
            transition: all 0.3s ease;
        }

        .btn-send-otp:hover {
            background-color: #4a8c11;
        }

        /* OTP Squares Container */
        .otp-container {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 15px;
        }

        .otp-box {
            width: 100%;
            height: 60px;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 15px;
            text-align: center;
            color: #ffffff;
            font-size: 24px;
            transition: all 0.3s ease;
        }

        .otp-box::placeholder {
            color: rgba(255, 255, 255, 0.5);
            font-size: 20px;
        }

        .otp-box:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #ffffff;
            outline: none;
            box-shadow: none;
        }

        /* Green Main Button */
        .btn-green {
            background-color: #5aa715;
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

        /* Resend Text */
        .resend-wrapper {
            text-align: center;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .resend-link {
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            font-size: 13px;
        }

        .resend-link:hover {
            color: #e0e0e0;
        }

        /* Logo Styling */
        .logo-text {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        .logo-cogo { color: #00c853; }
        .logo-smart { color: #333; }
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

                            <h3 class="fw-bold text-white mt-3">Forgot Password</h3>
                        </div>

                        <form action="" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="glass-label">Enter your Mobile Number</label>
                                <div class="position-relative">
                                    <i class="fas fa-phone-alt input-icon-left"></i>
                                    <input type="text" class="form-control glass-input glass-input-mobile" name="mobile" placeholder="8438298692" value="{{ old('mobile') }}" maxlength="10" required>
                                    <button type="button" class="btn-send-otp">Send OTP</button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="glass-label">Enter your Credentials</label>
                                <div class="otp-container">
                                    <input type="text" class="otp-box" maxlength="1" placeholder="*">
                                    <input type="text" class="otp-box" maxlength="1" placeholder="*">
                                    <input type="text" class="otp-box" maxlength="1" placeholder="*">
                                    <input type="text" class="otp-box" maxlength="1" placeholder="*">
                                </div>
                            </div>

                            <div class="resend-wrapper">
                                Didn't receive the code ? <br>
                                <a href="#" class="resend-link">Resend OTP</a>
                            </div>

                            <button type="submit" class="btn btn-green w-100 mt-2 shadow-sm">Verify</button>

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
        // Optional: Script to auto-focus the next OTP box when typing
        const otpBoxes = document.querySelectorAll('.otp-box');
        otpBoxes.forEach((box, index) => {
            box.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < otpBoxes.length - 1) {
                    otpBoxes[index + 1].focus();
                }
            });
            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                    otpBoxes[index - 1].focus();
                }
            });
        });
    </script>
</body>
</html>
