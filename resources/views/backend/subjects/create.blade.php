
@extends('backend.partial.master')
@section('title', 'Subject')
@section('sub_title', 'Add Subject')
@section('backend-content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title m-0">Add New Subject</h4>
                <a href="{{ route('subjects.index') }}" class="btn btn-sm btn-danger">
                    <i data-feather="arrow-left" class="me-1" style="width:14px;height:14px;"></i> Back
                </a>
            </div>

            <form action="{{ route('subjects.store') }}" method="post">
                @csrf

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 form-group mb-2 d-none">
                            <label for="course_type">Course Type<span class="text-danger">*</span></label>
                            <select name="course_type" id="course_type" class="form-control">
                                <option value="Semester Based" selected>Semester Based</option>
                            </select>
                            @error('course_type')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group mb-2">
                            <label for="course_id">Select Course<span class="text-danger">*</span></label>
                            <select name="course_id" id="course_id" class="form-control">
                                <option value="">--Select--</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}"
                                        {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group mb-2">
                            <label for="scheme_id">Select Scheme</label>
                            <select name="scheme_id" id="scheme_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>
                            @error('scheme_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group mb-2">
                            <label for="branch_id">Select Branch<span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>
                            @error('branch_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group mb-2" id="semester_container" style="display:none;">
                            <label for="semester_id">Select Semester</label>
                            <select name="semester_id" id="semester_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>
                            @error('semester_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group mb-2" id="annual_semester_container" style="display:none;">
                            <label for="annual_semester_id">Select Annual Terms</label>
                            <select name="annual_semester_id" id="annual_semester_id" class="form-control">
                                <option value="">--Select--</option>
                            </select>
                            @error('annual_semester_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 form-group mb-2">
                            <label>Subject Name<span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group mb-2">
                            <label>Subject Code</label>
                            <input type="text" name="subject_code" class="form-control" value="{{ old('subject_code') }}">
                            @error('subject_code')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group mb-2">
                            <label>Subject Type<span class="text-danger">*</span></label>
                            <select name="type" class="form-control">
                                <option value="Theory" {{ old('type') == 'Theory' ? 'selected' : '' }}>Theory</option>
                                <option value="Practical" {{ old('type') == 'Practical' ? 'selected' : '' }}>Practical</option>
                            </select>
                            @error('type')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group mb-2">
                            <label>Theory Credits</label>
                            <input type="text" name="theory_credits" class="form-control" value="{{ old('theory_credits') }}">
                            @error('theory_credits')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group mb-2">
                            <label>Practical Credits</label>
                            <input type="text" name="practical_credits" class="form-control" value="{{ old('practical_credits') }}">
                            @error('practical_credits')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group mb-2">
                            <label>Total Credits<span class="text-danger">*</span></label>
                            <input type="text" name="credits" class="form-control" value="{{ old('credits') }}">
                            @error('credits')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group mb-2">
                            <label>Total Internal Marks<span class="text-danger">*</span></label>
                            <input type="text" name="internal_marks" class="form-control" value="{{ old('internal_marks') }}">
                            @error('internal_marks')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group mb-2">
                            <label>External Marks<span class="text-danger">*</span></label>
                            <input type="text" name="external_marks" class="form-control" value="{{ old('external_marks') }}">
                            @error('external_marks')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group mb-2">
                            <label>Total Marks<span class="text-danger">*</span></label>
                            <input type="text" name="total" class="form-control" value="{{ old('total') }}">
                            @error('total')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group mb-2">
                            <label>Is Non-Credit Course<span class="text-danger">*</span></label>
                            <br>
                            <input type="radio" name="is_nc" value="0"
                                {{ old('is_nc', '0') == '0' ? 'checked' : '' }}>
                            <label>No</label>

                            <input type="radio" name="is_nc" value="1"
                                {{ old('is_nc') == '1' ? 'checked' : '' }}>
                            <label>Yes</label>

                            @error('is_nc')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Add Subject</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>

<script>
$(document).ready(function () {

    function resetSemesterUI() {
        $('#semester_container').hide();
        $('#annual_semester_container').hide();

        $('#semester_id').html('<option value="">--Select Semester--</option>');
        $('#annual_semester_id').html('<option value="">--Select--</option>');
    }

    function loadCourseData(courseId) {

        if (!courseId) {
            resetSemesterUI();
            $('#branch_id').html('<option value="">--Select--</option>');
            $('#scheme_id').html('<option value="">--Select--</option>');
            return;
        }

        resetSemesterUI();

        $('#branch_id').html('<option>Please wait...</option>');
        $('#scheme_id').html('<option>Please wait...</option>');

        // FETCH BRANCHES
        $.get("{{ url('branches') }}/" + courseId, function (branches) {

            $('#branch_id').html('<option value="">--Select Branch--</option>');

            $.each(branches, function (_, branch) {
                $('#branch_id').append(
                    `<option value="${branch.id}">${branch.name}</option>`
                );
            });

        });

        // FETCH COURSE DETAILS
        $.get("{{ url('courses') }}/" + courseId + "/details", function (response) {

            // Schemes
            $('#scheme_id').html('<option value="">--Select Scheme--</option>');

            $.each(response.schemes, function (_, scheme) {
                $('#scheme_id').append(
                    `<option value="${scheme.id}">${scheme.name}</option>`
                );
            });

            let courseType = $('#course_type').val();

            // Semester Based
            if (courseType === 'Semester Based') {

                $('#semester_container').show();

                $('#semester_id').html('<option value="">--Select Semester--</option>');

                $.each(response.semesters, function (_, semester) {
                    $('#semester_id').append(
                        `<option value="${semester.id}">${semester.name}</option>`
                    );
                });

            }

            // Annual Based
            if (courseType === 'Annual Based') {

                $('#annual_semester_container').show();

                $('#annual_semester_id').html('<option value="">--Select--</option>');

                $.each(response.annual_semesters, function (_, annual) {
                    $('#annual_semester_id').append(
                        `<option value="${annual.id}">${annual.name}</option>`
                    );
                });

            }

        });

    }

    // COURSE CHANGE
    $('#course_id').on('change', function () {
        loadCourseData($(this).val());
    });

    // COURSE TYPE CHANGE
    $('#course_type').on('change', function () {

        resetSemesterUI();

        let courseId = $('#course_id').val();

        if (courseId) {
            loadCourseData(courseId);
        }

    });

    // AUTO LOAD ON PAGE LOAD
    let defaultCourse = $('#course_id').val();
    let defaultType = $('#course_type').val();

    if (defaultCourse && defaultType) {
        loadCourseData(defaultCourse);
    }

});
</script>

@endsection
