@extends('backend.partial.master')

@section('title', 'Mail Configuration')

@section('backend-content')

<div class="container-fluid">
    
    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-1"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    @endif


    {{-- Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle me-1"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    @endif


    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="row">

        <div class="col-lg-8">

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fa fa-envelope me-2"></i>
                        SMTP Mail Settings
                    </h5>
                </div>


                <form action="{{ route('backend.mail-config.update') }}"
                      method="POST">

                    @csrf

                    <div class="card-body">

                        {{-- Mailer --}}
                        <div class="row mb-3">

                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Mail Mailer
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="MAIL_MAILER"
                                        class="form-select @error('MAIL_MAILER') is-invalid @enderror"
                                        required>

                                    <option value="smtp"
                                        {{ old('MAIL_MAILER', $mailConfig['MAIL_MAILER']) == 'smtp' ? 'selected' : '' }}>
                                        SMTP
                                    </option>

                                    <option value="log"
                                        {{ old('MAIL_MAILER', $mailConfig['MAIL_MAILER']) == 'log' ? 'selected' : '' }}>
                                        Log
                                    </option>

                                </select>

                                @error('MAIL_MAILER')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            {{-- Encryption --}}
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Encryption
                                </label>

                                <select name="MAIL_ENCRYPTION"
                                        class="form-select @error('MAIL_ENCRYPTION') is-invalid @enderror">

                                    <option value="">
                                        None
                                    </option>

                                    <option value="tls"
                                        {{ old('MAIL_ENCRYPTION', $mailConfig['MAIL_ENCRYPTION']) == 'tls' ? 'selected' : '' }}>
                                        TLS
                                    </option>

                                    <option value="ssl"
                                        {{ old('MAIL_ENCRYPTION', $mailConfig['MAIL_ENCRYPTION']) == 'ssl' ? 'selected' : '' }}>
                                        SSL
                                    </option>

                                </select>

                                @error('MAIL_ENCRYPTION')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Host --}}
                        <div class="row mb-3">

                            <div class="col-md-8">

                                <label class="form-label fw-semibold">
                                    SMTP Host
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="MAIL_HOST"
                                       value="{{ old('MAIL_HOST', $mailConfig['MAIL_HOST']) }}"
                                       class="form-control @error('MAIL_HOST') is-invalid @enderror"
                                       placeholder="smtp.gmail.com"
                                       required>

                                @error('MAIL_HOST')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            {{-- Port --}}
                            <div class="col-md-4">

                                <label class="form-label fw-semibold">
                                    SMTP Port
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="number"
                                       name="MAIL_PORT"
                                       value="{{ old('MAIL_PORT', $mailConfig['MAIL_PORT']) }}"
                                       class="form-control @error('MAIL_PORT') is-invalid @enderror"
                                       placeholder="587"
                                       min="1"
                                       max="65535"
                                       required>

                                @error('MAIL_PORT')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Username --}}
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                SMTP Username / Email
                                <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="MAIL_USERNAME"
                                   value="{{ old('MAIL_USERNAME', $mailConfig['MAIL_USERNAME']) }}"
                                   class="form-control @error('MAIL_USERNAME') is-invalid @enderror"
                                   placeholder="your-email@example.com"
                                   autocomplete="off"
                                   required>

                            @error('MAIL_USERNAME')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- Password --}}
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                SMTP Password
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">

                                <input type="password"
                                       name="MAIL_PASSWORD"
                                       id="MAIL_PASSWORD"
                                       value="{{ old('MAIL_PASSWORD', $mailConfig['MAIL_PASSWORD']) }}"
                                       class="form-control @error('MAIL_PASSWORD') is-invalid @enderror"
                                       placeholder="Enter SMTP password"
                                       autocomplete="new-password"
                                       required>

                                <button type="button"
                                        class="btn btn-outline-secondary"
                                        id="toggleMailPassword">

                                    <i class="fa fa-eye"
                                       id="passwordIcon"></i>

                                </button>

                            </div>

                            @error('MAIL_PASSWORD')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- From Address --}}
                        <div class="row mb-3">

                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    From Email Address
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="email"
                                       name="MAIL_FROM_ADDRESS"
                                       value="{{ old('MAIL_FROM_ADDRESS', $mailConfig['MAIL_FROM_ADDRESS']) }}"
                                       class="form-control @error('MAIL_FROM_ADDRESS') is-invalid @enderror"
                                       placeholder="noreply@example.com"
                                       required>

                                @error('MAIL_FROM_ADDRESS')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            {{-- From Name --}}
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    From Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="MAIL_FROM_NAME"
                                       value="{{ old('MAIL_FROM_NAME', $mailConfig['MAIL_FROM_NAME']) }}"
                                       class="form-control @error('MAIL_FROM_NAME') is-invalid @enderror"
                                       placeholder="Your Application Name"
                                       required>

                                @error('MAIL_FROM_NAME')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                    </div>


                    {{-- Footer --}}
                    <div class="card-footer bg-light">

                        <div class="d-flex justify-content-end">

                            <button type="submit"
                                    class="btn btn-primary">

                                <i class="fa fa-save me-1"></i>
                                Save Mail Configuration

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        {{-- Information Card --}}
        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fa fa-info-circle me-2"></i>
                        Mail Configuration
                    </h5>
                </div>

                <div class="card-body">

                    <p class="text-muted">
                        These settings are used by Laravel when sending
                        application emails.
                    </p>

                    <hr>

                    <h6 class="fw-bold">
                        Common SMTP Settings
                    </h6>

                    <div class="table-responsive">

                        <table class="table table-sm table-bordered mb-0">

                            <thead>
                                <tr>
                                    <th>Provider</th>
                                    <th>Host</th>
                                    <th>Port</th>
                                </tr>
                            </thead>

                            <tbody>

                                <tr>
                                    <td>Gmail</td>
                                    <td>smtp.gmail.com</td>
                                    <td>587</td>
                                </tr>

                                <tr>
                                    <td>Hostinger</td>
                                    <td>smtp.hostinger.com</td>
                                    <td>587</td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                    <hr>

                    <div class="alert alert-warning mb-0">

                        <i class="fa fa-exclamation-triangle me-1"></i>

                        <strong>Important:</strong>

                        <small>
                            SMTP passwords are stored in the application's
                            <code>.env</code> file. Make sure the file is
                            not publicly accessible.
                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- Password Toggle --}}
<script>

    document.getElementById('toggleMailPassword').addEventListener('click', function () {

        const passwordInput = document.getElementById('MAIL_PASSWORD');
        const passwordIcon = document.getElementById('passwordIcon');

        if (passwordInput.type === 'password') {

            passwordInput.type = 'text';

            passwordIcon.classList.remove('fa-eye');
            passwordIcon.classList.add('fa-eye-slash');

        } else {

            passwordInput.type = 'password';

            passwordIcon.classList.remove('fa-eye-slash');
            passwordIcon.classList.add('fa-eye');

        }

    });

</script>

@endsection