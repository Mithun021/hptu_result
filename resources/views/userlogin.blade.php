
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Log In | Student Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Guru Angad Dev Veterinary and Animal Sciences University"/>
    <meta name="author" content="Guru Angad Dev Veterinary and Animal Sciences University"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- App css -->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <script src="{{ asset('assets/js/head.js') }}"></script>

    <style>
        body {
            background: #f4f6fa;
        }

        .account-page {
            min-height: 100vh;
        }

        .login-card,
        .info-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            height: 100%;
        }
        .info-card {
            height: 500px;
            overflow-y: scroll
        }

        .login-card .card-body {
            padding: 35px 30px;
        }

        .info-card .card-body {
            padding: 35px;
        }

        .auth-title-section h2 {
            font-size: 27px;
            font-weight: 700;
        }

        .auth-title-section h5 {
            font-size: 15px;
            color: #6c757d !important;
            font-weight: 400 !important;
            line-height: 1.6;
        }

        .form-label {
            font-weight: 600;
            color: #343a40;
            font-size: 14px;
        }

        .form-control {
            height: 45px;
            border-radius: 7px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            border-color: #5369f8;
            box-shadow: 0 0 0 3px rgba(83, 105, 248, 0.10);
        }

        .login-btn {
            height: 45px;
            border-radius: 7px;
            font-weight: 600;
        }

        .info-heading {
            font-size: 21px;
            font-weight: 700;
            color: #343a40;
            margin-bottom: 12px;
        }

        .info-description {
            color: #6c757d;
            line-height: 1.7;
            font-size: 14px;
        }

        .portal-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .portal-list li {
            position: relative;
            padding: 10px 10px 10px 30px;
            margin-bottom: 7px;
            background: #f8f9fc;
            border-radius: 6px;
            font-size: 14px;
            color: #495057;
            line-height: 1.5;
        }

        .portal-list li:before {
            content: "✓";
            position: absolute;
            left: 11px;
            color: #198754;
            font-weight: bold;
        }

        .contact-box {
            background: #f8f9fc;
            border-left: 3px solid #5369f8;
            border-radius: 6px;
            padding: 15px;
            margin-top: 12px;
        }

        .contact-box p {
            margin-bottom: 5px;
            font-size: 14px;
            color: #555;
        }

        .contact-box strong {
            color: #343a40;
            font-size: 14px;
        }

        .login-note {
            font-size: 13px;
            color: #6c757d;
        }

        @media (max-width: 767px) {
            .login-card .card-body,
            .info-card .card-body {
                padding: 25px 20px;
            }

            .info-card {
                margin-top: 20px;
            }

            .account-page .row.vh-100 {
                height: auto !important;
                min-height: 100vh;
            }
        }
    </style>
</head>

<body>

    <!-- Begin page -->
    <div class="account-page">

        <div class="container-fluid p-0">

            <div class="row g-0 px-3 py-3 min-vh-100 align-items-center">

                <!-- Login Section -->
                <div class="col-xl-6 col-lg-6 col-md-6">

                    <div class="row">
                        <div class="col-md-8 mx-auto">

                            <div class="card login-card">

                                <div class="card-body">

                                    <div class="mb-0 p-0 p-lg-3">

                                        <div class="mb-0 border-0 p-md-4 p-lg-0">

                                            <!-- Login Heading -->
                                            <div class="auth-title-section mb-4 text-lg-start text-center">

                                                <h2 class="text-primary mb-3">
                                                    Student Login
                                                </h2>

                                                <h5 class="text-dark mb-3">
                                                    Welcome back! Please Sign in to continue.
                                                </h5>

                                            </div>

                                            <!-- Login Form -->
                                            <div class="pt-0">

                                                <form method="post"
                                                    action="{{ route('student.login.submit') }}"
                                                    class="my-4">

                                                    @csrf

                                                    <!-- University Roll Number -->
                                                    <div class="form-group mb-3">

                                                        <label for="roll_no" class="form-label">
                                                            University Roll No
                                                        </label>

                                                        <input
                                                            class="form-control"
                                                            type="text"
                                                            name="roll_no"
                                                            id="roll_no"
                                                            value="{{ old('roll_no') }}"
                                                            placeholder="Enter university roll no">

                                                    </div>

                                                    <!-- Password -->
                                                    <div class="form-group mb-3">

                                                        <label for="password" class="form-label">
                                                            Password
                                                        </label>

                                                        <input
                                                            type="password"
                                                            class="form-control"
                                                            name="password"
                                                            id="password"
                                                            placeholder="Enter your password">

                                                    </div>

                                                    <!-- Remember Me & Forgot Password -->
                                                    <div class="form-group d-flex mb-3">

                                                        <div class="col-sm-6">

                                                            <div class="form-check">

                                                                <input
                                                                    type="checkbox"
                                                                    class="form-check-input"
                                                                    id="checkbox-signin"
                                                                    checked>

                                                                <label
                                                                    class="form-check-label"
                                                                    for="checkbox-signin">
                                                                    Remember me
                                                                </label>

                                                            </div>

                                                        </div>

                                                        <div class="col-sm-6 text-end">

                                                            <a class="text-muted fs-14"
                                                                href="{{ route('student.forgot-password') }}">
                                                                Forgot password?
                                                            </a>

                                                        </div>

                                                    </div>

                                                    <!-- Login Button -->
                                                    <div class="form-group mb-0 row">

                                                        <div class="col-12">

                                                            <div class="d-grid">

                                                                <button
                                                                    class="btn btn-primary login-btn fw-semibold"
                                                                    type="submit">
                                                                    Log In
                                                                </button>

                                                            </div>

                                                        </div>

                                                        <!-- Validation Errors -->
                                                        @if ($errors->any())

                                                            <div class="col-12">

                                                                <div class="alert alert-primary mt-3 mb-0" role="alert">
                                                                    {{ $errors->first() }}
                                                                </div>

                                                            </div>

                                                        @endif

                                                    </div>

                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                </div>

                <!-- Information Section -->
                <div class="col-xl-6 col-lg-6 col-md-6">

                    <div class="card info-card">

                        <div class="card-body">

                            <!-- Portal Introduction -->
                            <h3 class="info-heading">
                                Welcome to Student Portal
                            </h3>

                            <p class="info-description">
                                The Students' Portal is designed to provide students with
                                access to all their information and online services through
                                a single application. It offers a simple and convenient way
                                to access all student-related services in one place.
                            </p>

                            <hr class="my-4">

                            <!-- Student Services -->
                            <h5 class="fw-semibold mb-3">
                                Available Student Services
                            </h5>

                            <ul class="portal-list">

                                <li>
                                    Apply for Online Regular Examination Form
                                </li>

                                <li>
                                    Apply for Online Re-appear Examination Form
                                </li>

                                <li>
                                    Apply for Online Re-Evaluation Form
                                </li>

                                <li>
                                    Check Examination Form Status
                                </li>

                                <li>
                                    Download issued Admit Card
                                </li>

                                <li>
                                    Check Results for students admitted in Ist Semester
                                    w.e.f. 2023-24 Academic Session
                                </li>

                            </ul>

                            <hr class="my-4">

                            <!-- Contact Information -->
                            <h5 class="fw-semibold mb-3">
                                Need Assistance?
                            </h5>

                            <!-- Examination Queries -->
                            <div class="contact-box">

                                <strong>
                                    Examination / Re-Evaluation Form Queries
                                </strong>

                                <p class="mt-2">
                                    Email: itexamhptu@gmail.com
                                </p>

                                <p>
                                    Phone: 0172-226914
                                </p>

                            </div>

                            <!-- Result Queries -->
                            <div class="contact-box">

                                <strong>
                                    Result Related Queries
                                </strong>

                                <p class="mt-2">
                                    Email: examhptu@gmail.com
                                </p>

                                <p>
                                    Phone: 01972-226999
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- END wrapper -->

    <!-- Vendor -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/libs/waypoints/lib/jquery.waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jquery.counterup/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>

</body>
</html>
