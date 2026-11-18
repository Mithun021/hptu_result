
@extends('backend.partial.master')

@section('title', 'Portal Settings')
@section('sub_title', 'Portal Feature Settings')

@section('backend-content')

<div class="row">
    <div class="col-md-8">

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Portal Feature Settings</h5>
            </div>

            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('setting.update') }}" method="POST">
                    @csrf

                    {{-- Cutlist --}}
                    <div class="form-check form-switch mb-4">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="cutlist"
                            id="cutlist"
                            value="1"
                            @checked(old('cutlist', $settings->cutlist))
                        >

                        <label class="form-check-label" for="cutlist">
                            <strong>Cutlist</strong>
                        </label>
                    </div>

                    {{-- Attendance Sheet --}}
                    <div class="form-check form-switch mb-4">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="attendance_sheet"
                            id="attendance_sheet"
                            value="1"
                            @checked(old('attendance_sheet', $settings->attendance_sheet))
                        >

                        <label class="form-check-label" for="attendance_sheet">
                            <strong>Attendance Sheet</strong>
                        </label>
                    </div>

                    {{-- Profile --}}
                    <div class="form-check form-switch mb-4">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="profile"
                            id="profile"
                            value="1"
                            @checked(old('profile', $settings->profile))
                        >

                        <label class="form-check-label" for="profile">
                            <strong>Profile</strong>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Settings
                    </button>

                </form>

            </div>
        </div>

    </div>
</div>

@endsection
