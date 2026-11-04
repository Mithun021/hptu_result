
@extends('backend.partial.master')
@section('title', 'Subject')
@section('sub_title', 'Edit Subject')
@section('backend-content')

<div class="row">
    <div class="col-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title m-0">Edit Subject</h4>
                <a href="{{ route('subjects.index') }}" class="btn btn-sm btn-danger">
                    <i data-feather="arrow-left" class="me-1"></i> Back
                </a>
            </div>

            <form action="{{ route('subjects.update', $subject->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">
                    <div class="row">

                        {{-- Course Type --}}
                        <div class="col-md-6 mb-2">
                            <label>Course Type *</label>
                            <select name="course_type" id="course_type" class="form-control">
                                <option value="">--Select--</option>
                                <option value="Annual Based" {{ old('course_type', $subject->course_type) == 'Annual Based' ? 'selected' : '' }}>Annual Based</option>
                                <option value="Semester Based" {{ old('course_type', $subject->course_type) == 'Semester Based' ? 'selected' : '' }}>Semester Based</option>
                            </select>
                        </div>

                        {{-- Course --}}
                        <div class="col-md-6 mb-2">
                            <label>Course *</label>
                            <select name="course_id" id="course_id" class="form-control">
                                <option value="">--Select--</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}"
                                        {{ old('course_id', $subject->course_id) == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Scheme --}}
                        <div class="col-md-4 mb-2">
                            <label>Scheme</label>
                            <select name="scheme_id" id="scheme_id" class="form-control"></select>
                        </div>

                        {{-- Branch --}}
                        <div class="col-md-4 mb-2">
                            <label>Branch *</label>
                            <select name="branch_id" id="branch_id" class="form-control"></select>
                        </div>

                        {{-- Semester --}}
                        <div class="col-md-4 mb-2" id="semester_container" style="display:none;">
                            <label>Semester</label>
                            <select name="semester_id" id="semester_id" class="form-control"></select>
                        </div>

                        {{-- Annual Term --}}
                        <div class="col-md-4 mb-2" id="annual_semester_container" style="display:none;">
                            <label>Annual Terms</label>
                            <select name="annual_semester_id" id="annual_semester_id" class="form-control"></select>
                        </div>

                    </div>

                    <div class="row mt-3">

                        {{-- Subject Name --}}
                        <div class="col-md-6 mb-2">
                            <label>Subject Name *</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $subject->name) }}">
                        </div>

                        {{-- Subject Code --}}
                        <div class="col-md-6 mb-2">
                            <label>Subject Code</label>
                            <input type="text" name="subject_code" class="form-control"
                                   value="{{ old('subject_code', $subject->subject_code) }}">
                        </div>

                        {{-- Subject Type --}}
                        <div class="col-md-6 mb-2">
                            <label>Subject Type *</label>
                            <select name="type" class="form-control">
                                <option value="Theory" {{ old('type', $subject->type) == 'Theory' ? 'selected' : '' }}>Theory</option>
                                <option value="Practical" {{ old('type', $subject->type) == 'Practical' ? 'selected' : '' }}>Practical</option>
                            </select>
                        </div>

                        {{-- Theory Credits --}}
                        <div class="col-md-6 form-group mb-2">
                            <label>Theory Credits</label>
                            <input type="text" name="theory_credits" class="form-control"
                                   value="{{ old('theory_credits', $subject->theory_credits) }}">
                            @error('theory_credits')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Practical Credits --}}
                        <div class="col-md-6 form-group mb-2">
                            <label>Practical Credits</label>
                            <input type="text" name="practical_credits" class="form-control"
                                   value="{{ old('practical_credits', $subject->practical_credits) }}">
                            @error('practical_credits')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Total Credits --}}
                        <div class="col-md-6 mb-2">
                            <label>Total Credits <span class="text-danger">*</span></label>
                            <input type="text" name="credits" class="form-control"
                                   value="{{ old('credits', $subject->credits) }}">
                        </div>

                        {{-- Total Internal Marks --}}
                        <div class="col-md-4 form-group mb-2">
                            <label>Total Internal Marks <span class="text-danger">*</span></label>
                            <input type="text" name="internal_marks" class="form-control"
                                   value="{{ old('internal_marks', $subject->internal_marks) }}"
                                   placeholder="Internal Marks">
                        </div>

                        {{-- External Marks --}}
                        <div class="col-md-4 form-group mb-2">
                            <label>External Marks <span class="text-danger">*</span></label>
                            <input type="text" name="external_marks" class="form-control"
                                   value="{{ old('external_marks', $subject->external_marks) }}"
                                   placeholder="External Marks">
                        </div>

                        {{-- Total Marks --}}
                        <div class="col-md-4 form-group mb-2">
                            <label>Total Marks <span class="text-danger">*</span></label>
                            <input type="text" name="total" class="form-control"
                                   value="{{ old('total', $subject->total) }}"
                                   placeholder="Total">
                        </div>

                        {{-- Non-Credit Course --}}
                        <div class="col-md-4 form-group mb-2">
                            <label>Is Non-Credit Course <span class="text-danger">*</span></label>
                            <br>

                            <input type="radio" name="is_nc" value="0"
                                {{ old('is_nc', $subject->is_nc) == 0 ? 'checked' : '' }}>
                            <label>No</label>

                            <input type="radio" name="is_nc" value="1"
                                {{ old('is_nc', $subject->is_nc) == 1 ? 'checked' : '' }}>
                            <label>Yes</label>

                            @error('is_nc')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Update Subject</button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ================= SCRIPT ================= --}}
<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>

<script>
$(document).ready(function () {

    let oldBranch = "{{ old('branch_id', $subject->branch_id) }}";
    let oldScheme = "{{ old('scheme_id', $subject->scheme_id) }}";
    let oldSemester = "{{ old('semester_id', $subject->semester_id) }}";
    let oldAnnual = "{{ old('annual_semester_id', $subject->annual_semester_id) }}";

    function resetSemesterUI() {
        $('#semester_container, #annual_semester_container').hide();

        $('#semester_id').html('<option value="">--Select--</option>');
        $('#annual_semester_id').html('<option value="">--Select--</option>');
    }

    function loadCourseData(courseId) {

        if (!courseId) {
            $('#branch_id').html('<option value="">--Select--</option>');
            $('#scheme_id').html('<option value="">--Select--</option>');
            resetSemesterUI();
            return;
        }

        // Load Branches
        $.get("{{ url('branches') }}/" + courseId, function (branches) {

            $('#branch_id').html('<option value="">--Select--</option>');

            $.each(branches, function (_, b) {
                $('#branch_id').append(
                    `<option value="${b.id}" ${b.id == oldBranch ? 'selected' : ''}>${b.name}</option>`
                );
            });

        });

        // Load Course Details
        $.get("{{ url('courses') }}/" + courseId + "/details", function (res) {

            // Load Schemes
            $('#scheme_id').html('<option value="">--Select--</option>');

            $.each(res.schemes, function (_, s) {
                $('#scheme_id').append(
                    `<option value="${s.id}" ${s.id == oldScheme ? 'selected' : ''}>${s.name}</option>`
                );
            });

            let type = $('#course_type').val();

            // Semester Based
            if (type === 'Semester Based') {

                $('#semester_container').show();

                $('#semester_id').html('<option value="">--Select--</option>');

                $.each(res.semesters, function (_, s) {
                    $('#semester_id').append(
                        `<option value="${s.id}" ${s.id == oldSemester ? 'selected' : ''}>${s.name}</option>`
                    );
                });

            }

            // Annual Based
            if (type === 'Annual Based') {

                $('#annual_semester_container').show();

                $('#annual_semester_id').html('<option value="">--Select--</option>');

                $.each(res.annual_semesters, function (_, a) {
                    $('#annual_semester_id').append(
                        `<option value="${a.id}" ${a.id == oldAnnual ? 'selected' : ''}>${a.name}</option>`
                    );
                });

            }

        });

    }

    // Course Change
    $('#course_id').change(function () {

        oldBranch = '';
        oldScheme = '';
        oldSemester = '';
        oldAnnual = '';

        resetSemesterUI();

        loadCourseData($(this).val());

    });

    // Course Type Change
    $('#course_type').change(function () {

        oldSemester = '';
        oldAnnual = '';

        resetSemesterUI();

        loadCourseData($('#course_id').val());

    });

    // PAGE LOAD
    loadCourseData($('#course_id').val());

});
</script>

@endsection
