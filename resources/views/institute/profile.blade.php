
@extends('institute.partial.master')

@section('title', 'My Profile')

@section('institute-content')

<div class="row">

    {{-- PROFILE DETAILS --}}
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header">
                <h5 class="mb-0">Profile Details</h5>
            </div>

            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Account Type</label>
                        <p class="form-control-plaintext">
                            {{ $isInstitute ? 'Institute' : 'Teacher' }}
                        </p>
                    </div>

                    @if($isInstitute)

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Institute Name</label>
                            <p class="form-control-plaintext">
                                {{ $authUser->name ?? '-' }}
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Short Name</label>
                            <p class="form-control-plaintext">
                                {{ $authUser->sort_name ?? '-' }}
                            </p>
                        </div>

                    @else

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teacher Name</label>
                            <p class="form-control-plaintext">
                                {{ $authUser->name ?? '-' }}
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Institute Name</label>
                            <p class="form-control-plaintext">
                                {{ $authUser->parentInstitute->name ?? '-' }}
                            </p>
                        </div>

                    @endif

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <p class="form-control-plaintext">
                            {{ $authUser->email ?? '-' }}
                        </p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Phone Number</label>
                        <p class="form-control-plaintext">
                            {{ $authUser->phone ?? '-' }}
                        </p>
                    </div>

                    @if($isInstitute)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">City</label>
                            <p class="form-control-plaintext">
                                {{ $authUser->city ?? '-' }}
                            </p>
                        </div>
                    @endif

                </div>

            </div>
        </div>
    </div>


    {{-- CHANGE PASSWORD --}}
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header">
                <h5 class="mb-0">Change Password</h5>
            </div>

            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('institute.profile.change-password') }}"
                      method="POST">

                    @csrf

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Current Password <span class="text-danger">*</span>
                            </label>

                            <input type="password"
                                   name="current_password"
                                   class="form-control"
                                   placeholder="Enter current password"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                New Password <span class="text-danger">*</span>
                            </label>

                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="Enter new password"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Confirm New Password <span class="text-danger">*</span>
                            </label>

                            <input type="password"
                                   name="password_confirmation"
                                   class="form-control"
                                   placeholder="Confirm new password"
                                   required>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-lock me-1"></i>
                        Change Password
                    </button>

                </form>

            </div>
        </div>
    </div>

</div>

@endsection
