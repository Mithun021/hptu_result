@extends('backend.partial.master')

@section('title', 'Edit Grace Marks Policy')
@section('sub_title', 'Edit Grace Marks')

@section('backend-content')

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">

            <div class="card-header">
                <h4 class="card-title m-0">Edit Grace Mark Policy</h4>
            </div>

            <form action="{{ route('grace-mark-policies.update', $policy->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="form-group mb-2">
                        <label>Course Type</label>

                        <select name="course_type" id="course_type" class="form-control">
                            <option value="">--Select--</option>

                            <option value="Semester Based"
                                {{ old('course_type', $policy->course_type) == 'Semester Based' ? 'selected' : '' }}>
                                Semester Based
                            </option>

                            <option value="Annual Based"
                                {{ old('course_type', $policy->course_type) == 'Annual Based' ? 'selected' : '' }}>
                                Annual Based
                            </option>
                        </select>

                        @error('course_type')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Course --}}
                    <div class="form-group mb-2">
                        <label>Select Course <span class="text-danger">*</span></label>

                        <select name="course_id" id="course_id" class="form-control">
                            <option value="">--Select--</option>

                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}"
                                    {{ old('course_id', $policy->course_id) == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('course_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Scheme --}}
                    <div class="form-group mb-2">
                        <label>Select Scheme</label>

                        <select name="scheme_id" id="scheme_id" class="form-control">
                            <option value="">--Select--</option>
                        </select>

                        @error('scheme_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Branch --}}
                    <div class="form-group mb-2">
                        <label>Select Branch <span class="text-danger">*</span></label>

                        <select name="branch_id" id="branch_id" class="form-control">
                            <option value="">--Select--</option>
                        </select>

                        @error('branch_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Batch --}}
                    <div class="form-group mb-2">
                        <label>Session / Batch <span class="text-danger">*</span></label>

                        <select name="batch_id" id="batch_id" class="form-control">
                            <option value="">--Select--</option>
                        </select>

                        @error('batch_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Academic Year --}}
                    <div class="form-group mb-2">
                        <label>Academic Year</label>

                        <input type="number"
                               class="form-control"
                               name="academic_year"
                               value="{{ old('academic_year', $policy->academic_year) }}">

                        @error('academic_year')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Grace Marks --}}
                    <div class="form-group mb-2">
                        <label>Max Grace Marks</label>

                        <input type="text"
                               class="form-control"
                               name="max_grace_marks"
                               value="{{ old('max_grace_marks', $policy->max_grace_marks) }}">

                        @error('max_grace_marks')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- <div class="form-group mb-3">
                        <label>Status</label>

                        <select name="status" class="form-control">
                            <option value="1"
                                {{ old('status', $policy->status) == 1 ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="0"
                                {{ old('status', $policy->status) == 0 ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div> --}}

                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">
                            Update Policy
                        </button>

                        <a href="{{ route('grace-mark-policies.index') }}"
                           class="btn btn-secondary">
                            Back
                        </a>
                    </div>

                </div>
            </form>

        </div>
    </div>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>

<script>
$(document).ready(function () {

    let selectedBranchId = "{{ old('branch_id', $policy->branch_id) }}";
    let selectedSchemeId = "{{ old('scheme_id', $policy->scheme_id) }}";
    let selectedBatchId  = "{{ old('batch_id', $policy->batch_id) }}";

    function loadCourseData(courseId)
    {
        if (!courseId) {
            return;
        }

        $('#branch_id').html('<option>Please wait...</option>');
        $('#scheme_id').html('<option>Please wait...</option>');
        $('#batch_id').html('<option>Please wait...</option>');

        /*
        |--------------------------------------------------------------------------
        | Fetch Branches
        |--------------------------------------------------------------------------
        */
        $.get("{{ url('branches') }}/" + courseId, function (branches) {

            $('#branch_id').html('<option value="">--Select Branch--</option>');

            $.each(branches, function (_, branch) {

                let selected = selectedBranchId == branch.id
                    ? 'selected'
                    : '';

                $('#branch_id').append(
                    `<option value="${branch.id}" ${selected}>
                        ${branch.name}
                    </option>`
                );
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Fetch Course Details
        |--------------------------------------------------------------------------
        */
        $.get("{{ url('courses') }}/" + courseId + "/details", function (response) {

            /*
            |--------------------------------------------------------------------------
            | Schemes
            |--------------------------------------------------------------------------
            */
            $('#scheme_id').html('<option value="">--Select Scheme--</option>');

            $.each(response.schemes, function (_, scheme) {

                let selected = selectedSchemeId == scheme.id
                    ? 'selected'
                    : '';

                $('#scheme_id').append(
                    `<option value="${scheme.id}" ${selected}>
                        ${scheme.name}
                    </option>`
                );
            });

            /*
            |--------------------------------------------------------------------------
            | Batches
            |--------------------------------------------------------------------------
            */
            $('#batch_id').html('<option value="">--Select Batch--</option>');

            $.each(response.batches, function (_, batch) {

                let selected = selectedBatchId == batch.id
                    ? 'selected'
                    : '';

                $('#batch_id').append(
                    `<option value="${batch.id}" ${selected}>
                        ${batch.name}
                    </option>`
                );
            });

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Course Change
    |--------------------------------------------------------------------------
    */
    $('#course_id').on('change', function () {

        loadCourseData($(this).val());

    });

    /*
    |--------------------------------------------------------------------------
    | Initial Load
    |--------------------------------------------------------------------------
    */
    let courseId = $('#course_id').val();

    if (courseId) {
        loadCourseData(courseId);
    }

});
</script>

@endsection