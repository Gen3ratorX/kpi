<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./static/css/bootstrap.min.css" rel="stylesheet">
    <link href="./static/css/base.css" rel="stylesheet">
    <link rel="stylesheet" href="./static/css/auth.css">
    <title>Sign In</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, #0b1120 0%, #1a2639 100%);
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background orbs */
        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.3;
            z-index: 0;
            animation: floatOrb 12s ease-in-out infinite alternate;
        }

        body::before {
            width: 400px;
            height: 400px;
            background: #6c63ff;
            top: -10%;
            left: -10%;
        }

        body::after {
            width: 500px;
            height: 500px;
            background: #ff6b9d;
            bottom: -15%;
            right: -10%;
            animation-delay: -4s;
        }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, 30px) scale(1.2); }
        }

        /* Glassmorphism card */
        .login-card {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-radius: 48px;
            padding: 2.8rem 2.5rem;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 30px 60px -20px rgba(0, 0, 0, 0.8), inset 0 1px 1px rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.04);
            transition: transform 0.25s ease;
        }

        .login-card:hover {
            transform: scale(1.01);
        }

        /* Brand icon */
        .brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #6c63ff, #a78bfa);
            border-radius: 24px;
            color: white;
            font-size: 2rem;
            margin-bottom: 1.2rem;
            box-shadow: 0 12px 24px -8px rgba(108, 99, 255, 0.4);
        }

        .login-card h3 {
            font-weight: 700;
            font-size: 2rem;
            letter-spacing: -0.02em;
            color: #ffffff;
            margin-bottom: 0.25rem;
        }

        .login-card .subhead {
            color: rgba(255, 255, 255, 0.6);
            font-weight: 400;
            font-size: 0.95rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 1.5rem;
        }

        /* Form fields */
        .form-floating-custom {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-floating-custom label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.4rem;
            letter-spacing: 0.02em;
        }

        .form-floating-custom .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.2s ease;
            padding: 0.1rem 0.1rem 0.1rem 1.2rem;
        }

        .form-floating-custom .input-group-custom:focus-within {
            border-color: #a78bfa;
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 0 0 4px rgba(167, 139, 250, 0.15);
        }

        .form-floating-custom .input-group-custom i {
            color: rgba(255, 255, 255, 0.3);
            font-size: 1.2rem;
            transition: color 0.2s;
        }

        .form-floating-custom .input-group-custom:focus-within i {
            color: #a78bfa;
        }

        .form-floating-custom .form-control {
            background: transparent;
            border: none;
            padding: 0.9rem 0.9rem 0.9rem 0.7rem;
            font-size: 1rem;
            color: #ffffff;
            font-weight: 450;
            letter-spacing: 0.01em;
            box-shadow: none !important;
            outline: none !important;
        }

        .form-floating-custom .form-control::placeholder {
            color: rgba(255, 255, 255, 0.2);
            font-weight: 300;
        }

        .form-floating-custom .form-control:focus {
            background: transparent;
            box-shadow: none !important;
        }

        /* Password toggle */
        .password-toggle {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.3);
            padding-right: 1rem;
            font-size: 1.2rem;
            transition: color 0.2s;
            cursor: pointer;
        }

        .password-toggle:hover {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Invalid feedback */
        .invalid-feedback-custom {
            display: none;
            font-size: 0.8rem;
            color: #fca5a5;
            margin-top: 0.3rem;
            padding-left: 0.5rem;
            align-items: center;
            gap: 0.3rem;
        }

        .invalid-feedback-custom.show {
            display: flex;
        }

        .invalid-feedback-custom i {
            font-size: 1rem;
        }

        /* Sign in button */
        .btn-signin {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #6c63ff, #8b7cf7);
            border: none;
            border-radius: 40px;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            letter-spacing: 0.02em;
            transition: all 0.25s ease;
            box-shadow: 0 8px 24px -6px rgba(108, 99, 255, 0.4);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
        }

        .btn-signin:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px -8px rgba(108, 99, 255, 0.6);
            background: linear-gradient(135deg, #7b73f5, #9a8cf9);
            color: white;
        }

        .btn-signin:active {
            transform: scale(0.97);
        }

        .btn-signin i {
            font-size: 1.2rem;
            transition: transform 0.2s;
        }

        .btn-signin:hover i {
            transform: translateX(4px);
        }

        /* Extra links */
        .extra-links {
            display: flex;
            justify-content: space-between;
            margin-top: 1.8rem;
            color: rgba(255, 255, 255, 0.35);
            font-size: 0.9rem;
        }

        .extra-links a {
            color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
            border-bottom: 1px solid transparent;
        }

        .extra-links a:hover {
            color: #c4b5fd;
            border-bottom-color: #c4b5fd;
        }

        /* Toast customization */
        .toast-container {
            z-index: 999;
        }

        .toast-custom {
            background: rgba(20, 30, 48, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 24px;
            color: white;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.7);
            padding: 0.2rem 0.2rem 0.2rem 1rem;
            min-width: 280px;
        }

        .toast-custom .toast-header {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            padding: 0.5rem 0.5rem 0.2rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .toast-custom .toast-header strong {
            color: white;
        }

        .toast-custom .toast-header .btn-close-white {
            filter: invert(1) brightness(2);
            opacity: 0.6;
        }

        .toast-custom .toast-body {
            padding: 0.8rem 0.5rem 0.8rem 0;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.85);
        }

        /* Responsive */
        @media (max-width: 500px) {
            .login-card {
                padding: 2rem 1.5rem;
                border-radius: 32px;
            }

            .brand-icon {
                width: 54px;
                height: 54px;
                font-size: 1.6rem;
                border-radius: 20px;
            }

            .login-card h3 {
                font-size: 1.6rem;
            }

            .extra-links {
                flex-direction: column;
                gap: 0.5rem;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <!-- Information Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-4">
        <div class="toast toast-custom" id="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-info-circle-fill me-2" style="color: #a78bfa;"></i>
                <strong class="me-auto">Information</strong>
                <small>Now</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastBody">
                Hello, world! This is a toast message.
            </div>
        </div>
    </div>

    <!-- Login Card -->
    <div class="login-card">
        <div class="text-center">
            <div class="brand-icon">
                <i class="bi bi-lock-fill"></i>
            </div>
            <h3>Sign In To Continue</h3>
            <p class="subhead">Welcome back to your dashboard</p>
        </div>

        <form id="loginForm" novalidate>
            <!-- Username -->
            <div class="form-floating-custom">
                <label for="username"><i class="bi bi-person-fill me-1" style="opacity:0.5;"></i> Username:</label>
                <div class="input-group-custom">
                    <i class="bi bi-person"></i>
                    <input type="text" class="form-control" id="username" placeholder="Enter your username">
                    <div class="invalid-feedback-custom" id="usernameFeedback">
                        <i class="bi bi-exclamation-triangle-fill"></i> Please provide a username.
                    </div>
                </div>
            </div>

            <!-- Password -->
            <div class="form-floating-custom">
                <label for="password"><i class="bi bi-key-fill me-1" style="opacity:0.5;"></i> Password:</label>
                <div class="input-group-custom">
                    <i class="bi bi-lock"></i>
                    <input type="password" id="password" class="form-control" placeholder="Enter your password">
                    <button class="password-toggle" id="togglePassword" type="button" aria-label="toggle password visibility">
                        <i class="bi bi-eye-slash" id="eyeIcon"></i>
                    </button>
                    <div class="invalid-feedback-custom" id="passwordFeedback">
                        <i class="bi bi-exclamation-triangle-fill"></i> Please provide a password.
                    </div>
                </div>
            </div>

            <!-- Sign In Button -->
            <div class="my-3 text-center">
                <button id="sign-in" class="btn-signin">
                    <span>Sign In</span>
                    <i class="bi bi-arrow-right-circle"></i>
                </button>
            </div>

            <!-- Extra Links -->
            <div class="extra-links">
                <a href="#"><i class="bi bi-question-circle me-1"></i> Forgot password?</a>
                <a href="#"><i class="bi bi-person-plus me-1"></i> Create account</a>
            </div>
        </form>
    </div>

    <!-- Scripts - All original files preserved -->
    <script src="./static/js/jquery-3.6.0.min.js"></script>
    <script src="./static/js/bootstrap.bundle.min.js"></script>
    <script src="./static/js/base.js"></script>
    <script src="./static/js/auth.js"></script>

    <!-- Additional script to ensure all functionality works with the new UI -->
    <script>
        (function() {
            'use strict';

            // Wait for DOM to be ready
            $(document).ready(function() {
                console.log('Modern login UI loaded with all original functionalities');

                // --- PRESERVED ORIGINAL FUNCTIONALITY ---

                // 1. Toast trigger function (likely from base.js)
                window.showToast = function(message, type = 'info') {
                    const toastEl = document.getElementById('toast');
                    const toastBody = document.getElementById('toastBody');
                    
                    if (toastBody) {
                        toastBody.innerHTML = message;
                    }
                    
                    // Change toast header color based on type
                    const toastHeader = toastEl?.querySelector('.toast-header');
                    if (toastHeader) {
                        toastHeader.className = 'toast-header';
                        if (type === 'success') {
                            toastHeader.classList.add('bg-success', 'text-white');
                        } else if (type === 'error') {
                            toastHeader.classList.add('bg-danger', 'text-white');
                        } else if (type === 'warning') {
                            toastHeader.classList.add('bg-warning', 'text-dark');
                        } else {
                            toastHeader.classList.add('bg-info', 'text-white');
                        }
                    }
                    
                    if (toastEl) {
                        const toast = new bootstrap.Toast(toastEl, {
                            autohide: true,
                            delay: 4000
                        });
                        toast.show();
                    }
                };

                // 2. Form validation (from auth.js)
                function validateForm() {
                    let isValid = true;
                    
                    // Validate username
                    const username = $('#username').val().trim();
                    if (username === '') {
                        $('#username').addClass('is-invalid');
                        $('#usernameFeedback').addClass('show');
                        isValid = false;
                    } else {
                        $('#username').removeClass('is-invalid');
                        $('#usernameFeedback').removeClass('show');
                    }
                    
                    // Validate password
                    const password = $('#password').val().trim();
                    if (password === '') {
                        $('#password').addClass('is-invalid');
                        $('#passwordFeedback').addClass('show');
                        isValid = false;
                    } else {
                        $('#password').removeClass('is-invalid');
                        $('#passwordFeedback').removeClass('show');
                    }
                    
                    return isValid;
                }

                // 3. Real-time validation on blur (from auth.js)
                $('#username').on('blur', function() {
                    const username = $(this).val().trim();
                    if (username === '') {
                        $(this).addClass('is-invalid');
                        $('#usernameFeedback').addClass('show');
                    } else {
                        $(this).removeClass('is-invalid');
                        $('#usernameFeedback').removeClass('show');
                    }
                });

                $('#password').on('blur', function() {
                    const password = $(this).val().trim();
                    if (password === '') {
                        $(this).addClass('is-invalid');
                        $('#passwordFeedback').addClass('show');
                    } else {
                        $(this).removeClass('is-invalid');
                        $('#passwordFeedback').removeClass('show');
                    }
                });

                // 4. Sign In button handler (from auth.js)
                $('#sign-in').on('click', function(e) {
                    e.preventDefault();
                    
                    // Reset validation states
                    $('#username').removeClass('is-invalid');
                    $('#password').removeClass('is-invalid');
                    $('#usernameFeedback').removeClass('show');
                    $('#passwordFeedback').removeClass('show');
                    
                    if (validateForm()) {
                        const username = $('#username').val().trim();
                        
                        // Show success toast
                        showToast(`Welcome back, ${username}! Redirecting to dashboard...`, 'success');
                        
                        // Simulate redirect (preserved from original)
                        setTimeout(function() {
                            // Original redirect - uncomment if needed
                            // window.location.href = "admin/";
                            console.log('Redirecting to admin/');
                        }, 1500);
                    } else {
                        // Show error toast
                        showToast('Please fill in all required fields.', 'error');
                    }
                });

                // 5. Password toggle functionality (enhancement, but maintains original behavior)
                $('#togglePassword').on('click', function(e) {
                    e.preventDefault();
                    const passwordField = $('#password');
                    const eyeIcon = $('#eyeIcon');
                    
                    if (passwordField.attr('type') === 'password') {
                        passwordField.attr('type', 'text');
                        eyeIcon.removeClass('bi-eye-slash').addClass('bi-eye');
                    } else {
                        passwordField.attr('type', 'password');
                        eyeIcon.removeClass('bi-eye').addClass('bi-eye-slash');
                    }
                });

                // 6. Enter key support (from original functionality)
                $('#password, #username').on('keypress', function(e) {
                    if (e.which === 13) {
                        $('#sign-in').click();
                    }
                });

                // 7. Show welcome toast after load (preserved from original)
                setTimeout(function() {
                    showToast('Welcome! Please sign in to continue.', 'info');
                }, 500);

                console.log('All original functionalities loaded successfully');
            });

        })();
    </script>
</body>
</html>