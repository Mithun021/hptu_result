@extends('institute.partial.master')

@section('title', 'Edit Assign Subjects')
@section('sub_title', 'Edit Assign Subjects To Teacher')

@section('institute-content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title m-0">Edit Assigned Subjects</h4>

        <a href="{{ route('assign.subject') }}" class="btn btn-sm btn-danger">
            Back
        </a>
    </div>

    <form action="{{ route('update.assign.subject', $assignment->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="row">

                {{-- Course Type --}}
                <div class="col-md-4 form-group mb-2 d-none">
                    <label>Course Type</label>

                    <select name="course_type" id="course_type" class="form-control">
                        <option value="Semester Based" selected>Semester Based</option>
                    </select>
                </div>

                {{-- Course --}}
                <div class="col-md-4 form-group mb-2">
                    <label>Select Course <span class="text-danger">*</span></label>

                    <select name="course_id" id="course_id" class="form-control" required>
                        <option value="">--Select Course--</option>

                        @foreach($courses as $course)
                            <option value="{{ $course->id }}"
                                {{ old('course_id', $selectedCourse) == $course->id ? 'selected' : '' }}>
                                {{ $course->name }}
                            </option>
                        @endforeach

                    </select>

                    @error('course_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Scheme --}}
                <div class="col-md-4 form-group mb-2">
                    <label>Select Scheme <span class="text-danger">*</span></label>

                    <select name="scheme_id" id="scheme_id" class="form-control" required>
                        <option value="">--Select Scheme--</option>
                    </select>

                    @error('scheme_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Branch --}}
                <div class="col-md-4 form-group mb-2">
                    <label>Select Branch <span class="text-danger">*</span></label>

                    <select name="branch_id" id="branch_id" class="form-control" required>
                        <option value="">--Select Branch--</option>
                    </select>

                    @error('branch_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Batch --}}
                <div class="col-md-4 form-group mb-2">
                    <label>Select Batch <span class="text-danger">*</span></label>

                    <select name="batch_id" id="batch_id" class="form-control" required>
                        <option value="">--Select Batch--</option>
                    </select>

                    @error('batch_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Semester --}}
                <div class="col-md-4 form-group mb-2" id="semester_container">
                    <label>Select Semester <span class="text-danger">*</span></label>

                    <select name="semester_id" id="semester_id" class="form-control" required>
                        <option value="">--Select Semester--</option>
                    </select>

                    @error('semester_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Teacher --}}
                <div class="col-md-4 form-group mb-2">
                    <label>Teacher</label>

                    <input type="text"
                           class="form-control"
                           value="{{ $teacher?->name ?? '-' }}"
                           readonly>
                </div>

                {{-- Subjects --}}
                <div class="col-md-12 form-group mb-2">

                    <label>
                        Select Subjects <span class="text-danger">*</span>
                    </label>

                    <div id="subject_checkbox_container"
                         class="border rounded p-3"
                         style="max-height:250px; overflow-y:auto;">

                        <div class="text-muted">
                            Loading subjects...
                        </div>

                    </div>

                    @error('subject_ids')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    @error('subject_ids.*')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                </div>

            </div>

        </div>

        <div class="card-footer">

            <button type="submit" class="btn btn-primary">
                Update Subjects
            </button>

            <a href="{{ route('assign.subject') }}" class="btn btn-secondary">
                Cancel
            </a>

        </div>

    </form>

</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>

<script>
$(document).ready(function () {

    /* =========================
       SELECTED VALUES
    ==========================*/

    let selectedScheme = "{{ old('scheme_id', $selectedScheme) }}";
    let selectedBranch = "{{ old('branch_id', $selectedBranch) }}";
    let selectedBatch = "{{ old('batch_id', $selectedBatch) }}";
    let selectedSemester = "{{ old('semester_id', $selectedSemester) }}";

    let assignedSubjects = @json(old('subject_ids', $assignedSubjectIds));

    assignedSubjects = assignedSubjects.map(id => String(id));

    /* =========================
       LOAD SUBJECTS
    ==========================*/

    function loadSubjects() {

        let courseType = $('#course_type').val();
        let courseId = $('#course_id').val();
        let schemeId = $('#scheme_id').val();
        let branchId = $('#branch_id').val();
        let batchId = $('#batch_id').val();
        let semesterId = $('#semester_id').val();

        if (!courseId || !schemeId || !branchId || !batchId || !semesterId) {
            $('#subject_checkbox_container').html(
                '<div class="text-muted">Select all filters to load subjects...</div>'
            );
            return;
        }

        $('#subject_checkbox_container').html(
            '<div class="text-muted">Loading subjects...</div>'
        );

        $.ajax({
            url: "{{ url('subjects/filter') }}",
            type: "GET",

            data: {
                course_type: courseType,
                course_id: courseId,
                scheme_id: schemeId,
                branch_id: branchId,
                batch_id: batchId,
                semester_id: semesterId,
                annual_semester_id: ''
            },

            success: function (subjects) {

                let html = '';

                if (subjects.length > 0) {

                    $.each(subjects, function (_, subject) {

                        let isChecked = assignedSubjects.includes(String(subject.id))
                            ? 'checked'
                            : '';

                        html += `
                            <div class="form-check mb-1">

                                <input class="form-check-input"
                                    type="checkbox"
                                    name="subject_ids[]"
                                    value="${subject.id}"
                                    id="subject_${subject.id}"
                                    ${isChecked}>

                                <label class="form-check-label"
                                    for="subject_${subject.id}">

                                    ${subject.name}
                                    (<span class="text-primary">${subject.subject_code ?? ''}</span>)

                                </label>

                            </div>
                        `;
                    });

                } else {

                    html = '<div class="text-danger">No Subject Found</div>';

                }

                $('#subject_checkbox_container').html(html);
            },

            error: function () {

                $('#subject_checkbox_container').html(
                    '<div class="text-danger">Unable to load subjects. Please try again.</div>'
                );

            }
        });
    }


    /* =========================
       LOAD COURSE DATA
    ==========================*/

    function loadCourseData(courseId) {

        if (!courseId) return;

        $('#scheme_id').html('<option value="">Loading...</option>');
        $('#branch_id').html('<option value="">Loading...</option>');
        $('#batch_id').html('<option value="">Loading...</option>');
        $('#semester_id').html('<option value="">Loading...</option>');

        $('#subject_checkbox_container').html(
            '<div class="text-muted">Loading course details...</div>'
        );

        let branchesLoaded = false;
        let detailsLoaded = false;

        let courseDetails = null;
        let branchesData = [];

        function finishLoading() {

            if (!branchesLoaded || !detailsLoaded) {
                return;
            }

            /* ---------- Branches ---------- */

            $('#branch_id').html('<option value="">--Select Branch--</option>');

            $.each(branchesData, function (_, branch) {

                let selected = String(selectedBranch) === String(branch.id)
                    ? 'selected'
                    : '';

                $('#branch_id').append(`
                    <option value="${branch.id}" ${selected}>
                        ${branch.name}
                    </option>
                `);

            });


            /* ---------- Schemes ---------- */

            $('#scheme_id').html('<option value="">--Select Scheme--</option>');

            $.each(courseDetails.schemes || [], function (_, scheme) {

                let selected = String(selectedScheme) === String(scheme.id)
                    ? 'selected'
                    : '';

                $('#scheme_id').append(`
                    <option value="${scheme.id}" ${selected}>
                        ${scheme.name}
                    </option>
                `);

            });


            /* ---------- Batches ---------- */

            $('#batch_id').html('<option value="">--Select Batch--</option>');

            $.each(courseDetails.batches || [], function (_, batch) {

                let selected = String(selectedBatch) === String(batch.id)
                    ? 'selected'
                    : '';

                $('#batch_id').append(`
                    <option value="${batch.id}" ${selected}>
                        ${batch.name}
                    </option>
                `);

            });


            /* ---------- Semesters ---------- */

            $('#semester_id').html('<option value="">--Select Semester--</option>');

            $.each(courseDetails.semesters || [], function (_, semester) {

                let selected = String(selectedSemester) === String(semester.id)
                    ? 'selected'
                    : '';

                $('#semester_id').append(`
                    <option value="${semester.id}" ${selected}>
                        ${semester.name}
                    </option>
                `);

            });

            loadSubjects();
        }


        /* ---------- Fetch Branches ---------- */

        $.get("{{ url('institute/branches') }}/" + courseId)

            .done(function (branches) {

                branchesData = branches;
                branchesLoaded = true;

                finishLoading();

            })

            .fail(function () {

                $('#branch_id').html(
                    '<option value="">Unable to load branches</option>'
                );

            });


        /* ---------- Fetch Course Details ---------- */

        $.get("{{ url('courses') }}/" + courseId + "/details")

            .done(function (response) {

                courseDetails = response;
                detailsLoaded = true;

                finishLoading();

            })

            .fail(function () {

                $('#subject_checkbox_container').html(
                    '<div class="text-danger">Unable to load course details.</div>'
                );

            });

    }


    /* =========================
       COURSE CHANGE
    ==========================*/

    $('#course_id').on('change', function () {

        selectedScheme = '';
        selectedBranch = '';
        selectedBatch = '';
        selectedSemester = '';

        // New course means previous subject selection is no longer applicable
        assignedSubjects = [];

        loadCourseData($(this).val());

    });


    /* =========================
       FILTER CHANGE
    ==========================*/

    $('#scheme_id, #branch_id, #batch_id, #semester_id').on('change', function () {

        selectedScheme = $('#scheme_id').val();
        selectedBranch = $('#branch_id').val();
        selectedBatch = $('#batch_id').val();
        selectedSemester = $('#semester_id').val();

        loadSubjects();

    });


    /* =========================
       INITIAL PAGE LOAD
    ==========================*/

    let defaultCourse = $('#course_id').val();

    if (defaultCourse) {
        loadCourseData(defaultCourse);
    }

});
</script>

@endsection
