
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8" />
    <title>Official Login | HPTU Examination Portal</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Himachal Pradesh Technical University (HPTU), Hamirpur"/>
    <meta name="author" content="Himachal Pradesh Technical University (HPTU), Hamirpur"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <script src="{{ asset('assets/js/head.js') }}"></script>

    <style>
        :root {
            --primary: #2457a6;
            --primary-dark: #173f80;
            --primary-light: #eef4ff;
            --dark: #18263d;
            --muted: #7b8798;
            --border: #e7ebf2;
            --bg: #f4f7fc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            /* font-family: inherit; */
            color: var(--dark);
        }

        /* =====================================
           SVG ICONS
        ===================================== */

        .svg-icon {
            width: 20px;
            height: 20px;
            display: inline-block;
            vertical-align: middle;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }

        .svg-icon-sm {
            width: 15px;
            height: 15px;
        }

        .svg-icon-lg {
            width: 25px;
            height: 25px;
        }

        /* =====================================
           MAIN LAYOUT
        ===================================== */

        .login-page {
            min-height: 100vh;
            padding: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 5% 10%, #e1ecff 0, transparent 32%),
                radial-gradient(circle at 95% 90%, #e3f1ff 0, transparent 30%),
                #f5f7fb;
        }

        .login-container {
            width: 100%;
            max-width: 1200px;
            min-height: 680px;
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 15px 55px rgba(25, 54, 100, 0.10);
            border: 1px solid #edf0f5;
        }

        /* =====================================
           LEFT LOGIN SECTION
        ===================================== */

        .login-section {
            padding: 55px 55px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #fff;
        }

        .university-brand {
            margin-bottom: 42px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            border-radius: 13px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .brand-title {
            font-size: 19px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .brand-subtitle {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.6;
            margin: 0;
        }

        .login-heading {
            margin-bottom: 28px;
        }

        .login-heading .welcome-text {
            font-size: 12px;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 10px;
        }

        .login-heading h2 {
            font-size: 29px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 9px;
        }

        .login-heading p {
            font-size: 13px;
            color: var(--muted);
            margin: 0;
        }

        .login-form .form-label {
            font-size: 12px;
            font-weight: 700;
            color: #39465a;
            margin-bottom: 8px;
        }

        .login-form .form-control {
            height: 47px;
            border: 1px solid #e1e6ef;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            background: #fbfcfe;
            box-shadow: none;
        }

        .login-form .form-control:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(36, 87, 166, 0.08);
        }

        .login-form .form-group {
            margin-bottom: 20px;
        }

        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
            font-size: 12px;
        }

        .login-options a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .login-options a:hover {
            text-decoration: underline;
        }

        .login-btn {
            height: 48px;
            width: 100%;
            border: none;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            transition: all .25s ease;
        }

        .login-btn:hover {
            background: var(--primary-dark);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(36, 87, 166, .18);
        }

        .login-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 25px 0 18px;
            color: #9aa4b3;
            font-size: 11px;
            font-weight: 600;
        }

        .login-divider::before,
        .login-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* =====================================
           COLLEGE / STUDENT LOGIN OPTIONS
        ===================================== */

        .portal-login-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .portal-login-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 12px;
            border: 1px solid var(--border);
            border-radius: 9px;
            text-decoration: none;
            transition: all .25s ease;
            background: #fff;
        }

        .portal-login-card:hover {
            border-color: var(--primary);
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .portal-icon {
            min-width: 36px;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .college-icon {
            background: #e8f7ee;
            color: #199b59;
        }

        .student-icon {
            background: #fff3e4;
            color: #e18b24;
        }

        .portal-text strong {
            display: block;
            font-size: 12px;
            color: var(--dark);
            font-weight: 700;
        }

        .portal-text small {
            display: block;
            color: var(--muted);
            font-size: 10px;
            margin-top: 3px;
        }

        .login-footer {
            margin-top: 32px;
            padding-top: 17px;
            border-top: 1px solid var(--border);
            font-size: 10px;
            color: #98a1ae;
            text-align: center;
        }

        /* =====================================
           RIGHT NOTIFICATION SECTION
        ===================================== */

        .notification-section {
            padding: 50px 45px;
            background: #f7f9fd;
            border-left: 1px solid #edf0f5;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .notification-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 15px;
        }

        .notification-heading h3 {
            font-size: 23px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 7px;
        }

        .notification-heading p {
            font-size: 12px;
            color: var(--muted);
            margin: 0;
        }

        .notification-live {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #e9f8ef;
            color: #159653;
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #1baa60;
            box-shadow: 0 0 0 4px rgba(27, 170, 96, .12);
        }

        /* Notification panel */

        .notification-panel {
            background: #fff;
            border: 1px solid #e8edf4;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 5px 20px rgba(30, 55, 90, .035);
        }

        .notification-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 14px;
            border-bottom: 1px solid #edf0f5;
            margin-bottom: 15px;
        }

        .notification-panel-header strong {
            font-size: 13px;
            font-weight: 800;
            color: var(--dark);
        }

        .notification-count {
            font-size: 10px;
            color: var(--primary);
            background: var(--primary-light);
            padding: 5px 9px;
            border-radius: 20px;
            font-weight: 700;
        }

        /* Fixed Height Marquee */

        .notification-marquee {
            height: 375px;
            overflow: hidden;
            position: relative;
        }

        .notification-track {
            display: flex;
            flex-direction: column;
            animation: notificationScroll 35s linear infinite;
        }

        .notification-marquee:hover .notification-track {
            animation-play-state: paused;
        }

        @keyframes notificationScroll {
            0% {
                transform: translateY(0);
            }

            100% {
                transform: translateY(-50%);
            }
        }

        .notification-item {
            display: flex;
            gap: 13px;
            padding: 15px 10px;
            border-bottom: 1px solid #edf0f5;
            text-decoration: none;
            transition: background .2s ease;
        }

        .notification-item:hover {
            background: #f8faff;
        }

        .notification-icon {
            min-width: 38px;
            width: 38px;
            height: 38px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-exam {
            background: #edf3ff;
            color: #3267c4;
        }

        .icon-result {
            background: #e8f8ef;
            color: #159653;
        }

        .icon-important {
            background: #fff1e8;
            color: #df7934;
        }

        .icon-supplementary {
            background: #f3edff;
            color: #8756d9;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-content h6 {
            font-size: 12px;
            font-weight: 700;
            color: #25334a;
            margin: 0 0 5px;
            line-height: 1.5;
        }

        .notification-content p {
            font-size: 11px;
            color: #7c8798;
            line-height: 1.6;
            margin: 0 0 7px;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .notice-tag {
            font-size: 9px;
            font-weight: 700;
            padding: 4px 7px;
            border-radius: 4px;
        }

        .tag-exam {
            color: #3267c4;
            background: #edf3ff;
        }

        .tag-result {
            color: #159653;
            background: #e8f8ef;
        }

        .tag-important {
            color: #df7934;
            background: #fff1e8;
        }

        .tag-general {
            color: #8756d9;
            background: #f3edff;
        }

        .notification-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 15px;
            padding-top: 14px;
            border-top: 1px solid #edf0f5;
        }

        .notification-footer span {
            font-size: 10px;
            color: var(--muted);
        }

        .notification-footer svg {
            color: var(--primary);
        }

        /* =====================================
           INFORMATION BOX
        ===================================== */

        .notice-info-box {
            margin-top: 20px;
            padding: 15px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #edf4ff;
            border: 1px solid #dce8ff;
            border-radius: 10px;
        }

        .notice-info-icon {
            color: var(--primary);
            margin-top: 1px;
        }

        .notice-info-box strong {
            font-size: 11px;
            color: var(--primary-dark);
            display: block;
            margin-bottom: 4px;
        }

        .notice-info-box p {
            font-size: 10px;
            color: #63748d;
            margin: 0;
            line-height: 1.6;
        }

        /* =====================================
           RESPONSIVE DESIGN
        ===================================== */

        @media (max-width: 991px) {

            .login-page {
                padding: 20px;
            }

            .login-container {
                grid-template-columns: 1fr;
                max-width: 600px;
            }

            .login-section {
                padding: 40px 35px;
            }

            .notification-section {
                padding: 40px 35px;
                border-left: 0;
                border-top: 1px solid #edf0f5;
            }
        }

        @media (max-width: 576px) {

            .login-page {
                padding: 12px;
            }

            .login-container {
                border-radius: 12px;
            }

            .login-section {
                padding: 30px 20px;
            }

            .notification-section {
                padding: 30px 20px;
            }

            .brand-title {
                font-size: 17px;
            }

            .login-heading h2 {
                font-size: 25px;
            }

            .notification-heading h3 {
                font-size: 19px;
            }

            .notification-live {
                font-size: 9px;
                padding: 6px 8px;
            }

            .notification-marquee {
                height: 350px;
            }

            .portal-login-options {
                gap: 8px;
            }

            .portal-login-card {
                padding: 10px 8px;
                gap: 7px;
            }

            .portal-text strong {
                font-size: 11px;
            }

            .portal-text small {
                font-size: 9px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .notification-track {
                animation: none !important;
            }

            .portal-login-card,
            .login-btn {
                transition: none !important;
            }
        }

    </style>

</head>

<body>

<!-- =====================================
     INLINE SVG ICON LIBRARY
===================================== -->

<svg xmlns="http://www.w3.org/2000/svg"
     style="display:none"
     aria-hidden="true">

    <symbol id="icon-graduation" viewBox="0 0 24 24">
        <path d="m2 10 10-5 10 5-10 5-10-5Z"/>
        <path d="M6 12v5c3.5 3 8.5 3 12 0v-5"/>
        <path d="M22 10v6"/>
    </symbol>

    <symbol id="icon-home" viewBox="0 0 24 24">
        <path d="m3 10 9-7 9 7"/>
        <path d="M5 9v12h14V9"/>
        <path d="M9 21v-7h6v7"/>
    </symbol>

    <symbol id="icon-user" viewBox="0 0 24 24">
        <circle cx="12" cy="8" r="4"/>
        <path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>
    </symbol>

    <symbol id="icon-login" viewBox="0 0 24 24">
        <path d="M10 17l5-5-5-5"/>
        <path d="M15 12H3"/>
        <path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/>
    </symbol>

    <symbol id="icon-calendar" viewBox="0 0 24 24">
        <rect x="3" y="5" width="18" height="16" rx="2"/>
        <path d="M16 3v4M8 3v4M3 10h18"/>
        <path d="m9 15 2 2 4-4"/>
    </symbol>

    <symbol id="icon-award" viewBox="0 0 24 24">
        <circle cx="12" cy="8" r="6"/>
        <path d="m8.2 13-1.2 8 5-3 5 3-1.2-8"/>
        <path d="m10 8 1.5 1.5L14 7"/>
    </symbol>

    <symbol id="icon-book" viewBox="0 0 24 24">
        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
        <path d="M8 7h8M8 11h8"/>
    </symbol>

    <symbol id="icon-file" viewBox="0 0 24 24">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
        <path d="M14 2v6h6M8 13h8M8 17h8"/>
    </symbol>

    <symbol id="icon-clipboard" viewBox="0 0 24 24">
        <rect x="4" y="4" width="16" height="18" rx="2"/>
        <path d="M9 4V2h6v2M8 10h8M8 14h8M8 18h5"/>
    </symbol>

    <symbol id="icon-check-circle" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/>
        <path d="m8 12 3 3 5-6"/>
    </symbol>

    <symbol id="icon-bell" viewBox="0 0 24 24">
        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
        <path d="M10 21h4"/>
    </symbol>

    <symbol id="icon-info" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/>
        <path d="M12 11v5M12 8h.01"/>
    </symbol>

    <symbol id="icon-arrow-up" viewBox="0 0 24 24">
        <path d="m5 12 7-7 7 7"/>
        <path d="M12 19V5"/>
    </symbol>

    <symbol id="icon-shield-check" viewBox="0 0 24 24">
        <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
        <path d="m9 12 2 2 4-4"/>
    </symbol>

</svg>


<!-- =====================================
     MAIN PAGE
===================================== -->

<div class="login-page">

    <div class="login-container">

        <!-- =====================================
             LEFT SECTION - OFFICIAL LOGIN
        ====================================== -->

        <div class="login-section">


            <!-- Login Heading -->

            <div class="login-heading">

                <div class="welcome-text">
                    Welcome Back
                </div>

                <h2>Official Login</h2>

                <p>
                    Sign in to access the university examination management portal.
                </p>

            </div>


            <!-- Official Login Form -->

            <form method="post"
                  action="{{ route('adminlogin.post') }}"
                  class="login-form">

                @csrf

                <div class="form-group">

                    <label for="emailaddress" class="form-label">
                        Email Address
                    </label>

                    <input
                        class="form-control"
                        type="email"
                        name="email"
                        id="emailaddress"
                        value="{{ old('email') }}"
                        placeholder="Enter your registered email"
                    >

                </div>


                <div class="form-group">

                    <label for="password" class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        name="password"
                        id="password"
                        placeholder="Enter your password"
                    >

                </div>


                <div class="login-options">

                    <div class="form-check">

                        <input
                            type="checkbox"
                            class="form-check-input"
                            id="checkbox-signin"
                            checked
                        >

                        <label class="form-check-label"
                               for="checkbox-signin">
                            Remember me
                        </label>

                    </div>

                    <a href="{{ route('user.forgot-password') }}">
                        Forgot password?
                    </a>

                </div>


                <button class="login-btn" type="submit">

                    <svg class="svg-icon svg-icon-sm me-1">
                        <use href="#icon-login"></use>
                    </svg>

                    Sign In to Portal

                </button>


                @if ($errors->any())

                    <div class="alert alert-danger mt-3 mb-0" role="alert">
                        {{ $errors->first() }}
                    </div>

                @endif

            </form>


            <!-- Login Divider -->

            <div class="login-divider">
                OR ACCESS YOUR PORTAL
            </div>


            <!-- College and Student Login -->

            <div class="portal-login-options">

                <!-- College Login -->

                <a href="{{ route('institute.login') }}"
                   class="portal-login-card">

                    <div class="portal-icon college-icon">

                        <svg class="svg-icon">
                            <use href="#icon-home"></use>
                        </svg>

                    </div>

                    <div class="portal-text">

                        <strong>College Login</strong>

                        <small>Institute Portal</small>

                    </div>

                </a>


                <!-- Student Login -->

                <a href="{{ route('student.login') }}"
                   class="portal-login-card">

                    <div class="portal-icon student-icon">

                        <svg class="svg-icon">
                            <use href="#icon-user"></use>
                        </svg>

                    </div>

                    <div class="portal-text">

                        <strong>Student Login</strong>

                        <small>Student Portal</small>

                    </div>

                </a>

            </div>


            <!-- Footer -->

            <div class="login-footer">

                © {{ date('Y') }} Himachal Pradesh Technical University (HPTU), Hamirpur.
                All Rights Reserved.

            </div>

        </div>


        <!-- =====================================
             RIGHT SECTION - NOTIFICATIONS
        ====================================== -->

        <div class="notification-section">


            <!-- Notification Panel -->

            <div class="notification-panel">

                <div class="notification-panel-header">

                    <strong>

                        <svg class="svg-icon svg-icon-sm me-1">
                            <use href="#icon-bell"></use>
                        </svg>

                        Latest Notifications

                    </strong>

                    <span class="notification-count">
                        Updates
                    </span>

                </div>


                <!-- Bottom to Top Scrolling -->

                <div class="notification-marquee">

                    <div class="notification-track">


                        <!-- Notification 1 -->

                        <div class="notification-item">

                            <div class="notification-icon icon-exam">

                                <svg class="svg-icon">
                                    <use href="#icon-calendar"></use>
                                </svg>

                            </div>

                            <div class="notification-content">

                                <h6>
                                    End Semester Examination
                                </h6>

                                <p>
                                    Students are advised to check their examination
                                    schedule and download their admit cards before examinations.
                                </p>

                                <div class="notification-meta">

                                    <span class="notice-tag tag-exam">
                                        Examination
                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- Notification 2 -->

                        <div class="notification-item">

                            <div class="notification-icon icon-result">

                                <svg class="svg-icon">
                                    <use href="#icon-award"></use>
                                </svg>

                            </div>

                            <div class="notification-content">

                                <h6>
                                    Regular Examination Results
                                </h6>

                                <p>
                                    Check the latest semester examination results
                                    through the official student portal.
                                </p>

                                <div class="notification-meta">

                                    <span class="notice-tag tag-result">
                                        Result Update
                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- Notification 3 -->

                        <div class="notification-item">

                            <div class="notification-icon icon-supplementary">

                                <svg class="svg-icon">
                                    <use href="#icon-book"></use>
                                </svg>

                            </div>

                            <div class="notification-content">

                                <h6>
                                    Supplementary Examination
                                </h6>

                                <p>
                                    Students can check supplementary examination
                                    information and related updates from the portal.
                                </p>

                                <div class="notification-meta">

                                    <span class="notice-tag tag-general">
                                        Supplementary
                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- Notification 4 -->

                        <div class="notification-item">

                            <div class="notification-icon icon-important">

                                <svg class="svg-icon">
                                    <use href="#icon-file"></use>
                                </svg>

                            </div>

                            <div class="notification-content">

                                <h6>
                                    Examination Form Submission
                                </h6>

                                <p>
                                    Ensure that all examination forms are submitted
                                    with the required details and documents.
                                </p>

                                <div class="notification-meta">

                                    <span class="notice-tag tag-important">
                                        Important
                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- Notification 5 -->

                        <div class="notification-item">

                            <div class="notification-icon icon-exam">

                                <svg class="svg-icon">
                                    <use href="#icon-clipboard"></use>
                                </svg>

                            </div>

                            <div class="notification-content">

                                <h6>
                                    Internal Assessment Marks
                                </h6>

                                <p>
                                    Institutes are advised to verify student internal
                                    assessment records before result processing.
                                </p>

                                <div class="notification-meta">

                                    <span class="notice-tag tag-exam">
                                        Assessment
                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- Notification 6 -->

                        <div class="notification-item">

                            <div class="notification-icon icon-result">

                                <svg class="svg-icon">
                                    <use href="#icon-check-circle"></use>
                                </svg>

                            </div>

                            <div class="notification-content">

                                <h6>
                                    Result Verification
                                </h6>

                                <p>
                                    Colleges should carefully verify student
                                    subject-wise marks and result records.
                                </p>

                                <div class="notification-meta">

                                    <span class="notice-tag tag-result">
                                        Verification
                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- =====================================
                             DUPLICATE NOTIFICATIONS
                             FOR SEAMLESS MARQUEE
                        ====================================== -->


                        <!-- Duplicate 1 -->

                        <div class="notification-item" aria-hidden="true">

                            <div class="notification-icon icon-exam">

                                <svg class="svg-icon">
                                    <use href="#icon-calendar"></use>
                                </svg>

                            </div>

                            <div class="notification-content">

                                <h6>End Semester Examination</h6>

                                <p>
                                    Students are advised to check their examination
                                    schedule and download their admit cards before examinations.
                                </p>

                                <div class="notification-meta">
                                    <span class="notice-tag tag-exam">Examination</span>
                                </div>

                            </div>

                        </div>


                        <!-- Duplicate 2 -->

                        <div class="notification-item" aria-hidden="true">

                            <div class="notification-icon icon-result">

                                <svg class="svg-icon">
                                    <use href="#icon-award"></use>
                                </svg>

                            </div>

                            <div class="notification-content">

                                <h6>Regular Examination Results</h6>

                                <p>
                                    Check the latest semester examination results
                                    through the official student portal.
                                </p>

                                <div class="notification-meta">
                                    <span class="notice-tag tag-result">Result Update</span>
                                </div>

                            </div>

                        </div>


                        <!-- Duplicate 3 -->

                        <div class="notification-item" aria-hidden="true">

                            <div class="notification-icon icon-supplementary">

                                <svg class="svg-icon">
                                    <use href="#icon-book"></use>
                                </svg>

                            </div>

                            <div class="notification-content">

                                <h6>Supplementary Examination</h6>

                                <p>
                                    Students can check supplementary examination
                                    information and related updates from the portal.
                                </p>

                                <div class="notification-meta">
                                    <span class="notice-tag tag-general">Supplementary</span>
                                </div>

                            </div>

                        </div>


                        <!-- Duplicate 4 -->

                        <div class="notification-item" aria-hidden="true">

                            <div class="notification-icon icon-important">

                                <svg class="svg-icon">
                                    <use href="#icon-file"></use>
                                </svg>

                            </div>

                            <div class="notification-content">

                                <h6>Examination Form Submission</h6>

                                <p>
                                    Ensure that all examination forms are submitted
                                    with the required details and documents.
                                </p>

                                <div class="notification-meta">
                                    <span class="notice-tag tag-important">Important</span>
                                </div>

                            </div>

                        </div>


                        <!-- Duplicate 5 -->

                        <div class="notification-item" aria-hidden="true">

                            <div class="notification-icon icon-exam">

                                <svg class="svg-icon">
                                    <use href="#icon-clipboard"></use>
                                </svg>

                            </div>

                            <div class="notification-content">

                                <h6>Internal Assessment Marks</h6>

                                <p>
                                    Institutes are advised to verify student internal
                                    assessment records before result processing.
                                </p>

                                <div class="notification-meta">
                                    <span class="notice-tag tag-exam">Assessment</span>
                                </div>

                            </div>

                        </div>


                        <!-- Duplicate 6 -->

                        <div class="notification-item" aria-hidden="true">

                            <div class="notification-icon icon-result">

                                <svg class="svg-icon">
                                    <use href="#icon-check-circle"></use>
                                </svg>

                            </div>

                            <div class="notification-content">

                                <h6>Result Verification</h6>

                                <p>
                                    Colleges should carefully verify student
                                    subject-wise marks and result records.
                                </p>

                                <div class="notification-meta">
                                    <span class="notice-tag tag-result">Verification</span>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Notification Footer -->

                <div class="notification-footer">

                    <span>

                        <svg class="svg-icon svg-icon-sm me-1">
                            <use href="#icon-info"></use>
                        </svg>

                        Hover over notifications to pause scrolling.

                    </span>

                    <svg class="svg-icon">
                        <use href="#icon-arrow-up"></use>
                    </svg>

                </div>

            </div>


            <!-- Information Box -->

            <div class="notice-info-box">

                <div class="notice-info-icon">

                    <svg class="svg-icon svg-icon-lg">
                        <use href="#icon-shield-check"></use>
                    </svg>

                </div>

                <div>

                    <strong>
                        Official University Portal
                    </strong>

                    <p>
                        Please use your registered login credentials.
                        For examination and result-related updates, regularly
                        check the official university notifications.
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- =====================================
     JAVASCRIPT
===================================== -->

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('assets/libs/waypoints/lib/jquery.waypoints.min.js') }}"></script>
<script src="{{ asset('assets/libs/jquery.counterup/jquery.counterup.min.js') }}"></script>

<script src="{{ asset('assets/js/app.js') }}"></script>

</body>
</html>
