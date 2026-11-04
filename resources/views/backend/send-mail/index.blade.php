@extends('backend.partial.master')

@section('title', 'Send Mail')

@section('backend-content')

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="row mb-3">

        <div class="col-12">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <h4 class="mb-1">
                        Send Mail
                    </h4>

                    <p class="text-muted mb-0">
                        Send an email to one or multiple recipients.
                    </p>
                </div>

            </div>

        </div>

    </div>


    {{-- Success Message --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show"
             role="alert">

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

        <div class="alert alert-danger alert-dismissible fade show"
             role="alert">

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

            <strong>
                Please fix the following errors:
            </strong>

            <ul class="mb-0 mt-2">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    <div class="row">

        {{-- Main Form --}}
        <div class="col-lg-8">

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">

                    <h5 class="mb-0">

                        <i class="fa fa-paper-plane me-2"></i>

                        Compose Mail

                    </h5>

                </div>


                <form action="{{ route('backend.send-mail.store') }}"
                      method="POST"
                      id="sendMailForm">

                    @csrf


                    <div class="card-body">


                        {{-- Recipients --}}
                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                Recipient Email(s)

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <textarea
                                name="emails"
                                id="emails"
                                rows="4"
                                class="form-control @error('emails') is-invalid @enderror"
                                placeholder="example@gmail.com, test@example.com&#10;another@example.com"
                                required>{{ old('emails') }}</textarea>


                            <div class="form-text">

                                You can enter multiple email addresses using
                                <strong>comma</strong> or
                                <strong>new line</strong>.

                                Maximum
                                <strong>10 emails</strong>
                                per send.

                            </div>


                            @error('emails')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror


                            {{-- Email Counter --}}
                            <div class="mt-2">

                                <span class="badge bg-secondary"
                                      id="emailCount">

                                    0 / 10

                                </span>

                                <span class="text-danger small d-none"
                                      id="emailLimitError">

                                    Maximum 10 email addresses allowed.

                                </span>

                            </div>

                        </div>


                        {{-- Subject --}}
                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                Subject

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <input type="text"
                                   name="subject"
                                   value="{{ old('subject') }}"
                                   class="form-control @error('subject') is-invalid @enderror"
                                   maxlength="255"
                                   placeholder="Enter mail subject"
                                   required>


                            @error('subject')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        {{-- Message --}}
                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                Message

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <textarea
                                name="message"
                                id="mailMessage"
                                rows="12"
                                class="form-control @error('message') is-invalid @enderror"
                                placeholder="Write your message here..."
                                required>{{ old('message') }}</textarea>


                            @error('message')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                    </div>


                    {{-- Footer --}}
                    <div class="card-footer bg-light">

                        <div class="d-flex justify-content-end gap-2">

                            <button type="reset"
                                    class="btn btn-secondary"
                                    id="resetMailForm">

                                <i class="fa fa-refresh me-1"></i>

                                Reset

                            </button>


                            <button type="submit"
                                    class="btn btn-primary"
                                    id="sendMailButton">

                                <i class="fa fa-paper-plane me-1"></i>

                                Send Mail

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        {{-- Information --}}
        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-header">

                    <h5 class="mb-0">

                        <i class="fa fa-info-circle me-2"></i>

                        Mail Information

                    </h5>

                </div>


                <div class="card-body">


                    <div class="alert alert-info">

                        <i class="fa fa-info-circle me-1"></i>

                        You can send mail to maximum
                        <strong>10 recipients</strong>
                        at one time.

                    </div>


                    <h6 class="fw-bold">
                        Recipient Format
                    </h6>


                    <p class="text-muted small mb-2">
                        You can use comma:
                    </p>

                    <div class="bg-light border rounded p-2 mb-3">

                        <code>
                            one@gmail.com, two@gmail.com, three@gmail.com
                        </code>

                    </div>


                    <p class="text-muted small mb-2">
                        Or use separate lines:
                    </p>

                    <div class="bg-light border rounded p-2 mb-3">

                        <code>
                            one@gmail.com<br>
                            two@gmail.com<br>
                            three@gmail.com
                        </code>

                    </div>


                    <hr>


                    <h6 class="fw-bold">
                        Sending Method
                    </h6>

                    <p class="text-muted small mb-0">

                        Each recipient receives the email separately.
                        Other recipients will not be visible to them.

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ================================
     JavaScript
================================ --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    const emailTextarea = document.getElementById('emails');
    const emailCount = document.getElementById('emailCount');
    const emailLimitError = document.getElementById('emailLimitError');
    const sendMailButton = document.getElementById('sendMailButton');
    const sendMailForm = document.getElementById('sendMailForm');


    function getEmails() {

        let value = emailTextarea.value.trim();

        if (!value) {
            return [];
        }

        let emails = value
            .split(/[\s,;]+/)
            .filter(email => email.trim() !== '')
            .map(email => email.trim().toLowerCase());

        // Remove duplicate emails
        emails = [...new Set(emails)];

        return emails;
    }


    function updateEmailCount() {

        let emails = getEmails();

        let count = emails.length;

        emailCount.innerText = count + ' / 10';


        if (count > 10) {

            emailCount.classList.remove('bg-secondary');
            emailCount.classList.add('bg-danger');

            emailLimitError.classList.remove('d-none');

            sendMailButton.disabled = true;

        } else {

            emailCount.classList.remove('bg-danger');
            emailCount.classList.add('bg-secondary');

            emailLimitError.classList.add('d-none');

            sendMailButton.disabled = false;
        }
    }


    emailTextarea.addEventListener('input', updateEmailCount);


    sendMailForm.addEventListener('submit', function (e) {

        let emails = getEmails();


        if (emails.length === 0) {

            e.preventDefault();

            alert('Please enter at least one email address.');

            return;
        }


        if (emails.length > 10) {

            e.preventDefault();

            alert('Maximum 10 email addresses are allowed.');

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Remove duplicate / normalize before submit
        |--------------------------------------------------------------------------
        */

        emailTextarea.value = emails.join(',');


        /*
        |--------------------------------------------------------------------------
        | Prevent Double Click
        |--------------------------------------------------------------------------
        */

        sendMailButton.disabled = true;

        sendMailButton.innerHTML =
            '<i class="fa fa-spinner fa-spin me-1"></i> Sending...';

    });


    /*
    |--------------------------------------------------------------------------
    | Reset
    |--------------------------------------------------------------------------
    */

    document.getElementById('resetMailForm')
        .addEventListener('click', function () {

            setTimeout(function () {

                updateEmailCount();

            }, 50);

        });


    // Initial count
    updateEmailCount();

});

</script>

@endsection