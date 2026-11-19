
@extends('institute.partial.master')

@section('title', 'Assign Subjects')
@section('sub_title', 'Assign Subjects to Teachers')

@section('institute-content')

<style>
    .batch-wrapper,
    .subject-wrapper {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        max-height: 250px;
        overflow-y: auto;
        background: #fff;
    }

    .batch-grid,
    .subject-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .batch-item,
    .subject-item {
        border: 1px solid #e5e7eb;
        padding: 9px 12px;
        border-radius: 4px;
        background: #fafafa;
        margin: 0;
        cursor: pointer;
    }

    .batch-item:hover,
    .subject-item:hover {
        background: #f0f5ff;
        border-color: #b8caff;
    }

    .batch-item input,
    .subject-item input {
        margin-right: 7px;
    }

    .selection-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .selection-count {
        font-size: 12px;
        color: #6c757d;
    }

    @media(max-width: 768px) {
        .batch-grid,
        .subject-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width: 480px) {
        .batch-grid,
        .subject-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title m-0">Create of Assign Subjects to Teachers</h4>
        <a href="{{ route('assign.subject') }}" class="btn btn-sm btn-danger">Back</a>
    </div>

    <form action="{{ route('save.assign.subject') }}" method="POST">
        @csrf

        <div class="card-body">

            <div class="row">

                {{-- COURSE TYPE --}}
                <div class="col-md-4 form-group mb-2 d-none">
                    <label for="course_type">Course Type</label>

                    <select name="course_type" id="course_type" class="form-control">
                        <option value="">--Select--</option>
                        <option value="Semester Based" selected>Semester Based</option>
                        <option value="Annual Based">Annual Based</option>
                    </select>

                    @error('course_type')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- COURSE --}}
                <div class="col-md-4 form-group mb-2">
                    <label for="course_id">
                        Course <span class="text-danger">*</span>
                    </label>

                    <select name="course_id" id="course_id" class="form-control" required>
                        <option value="">Select Course</option>

                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('course_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- SCHEME --}}
                <div class="col-md-4 form-group mb-2">
                    <label for="scheme_id">
                        Select Scheme <span class="text-danger">*</span>
                    </label>

                    <select name="scheme_id" id="scheme_id" class="form-control" required>
                        <option value="">--Select Scheme--</option>
                    </select>

                    @error('scheme_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- BRANCH --}}
                <div class="col-md-4 form-group mb-2">
                    <label for="branch_id">
                        Select Branch <span class="text-danger">*</span>
                    </label>

                    <select name="branch_id" id="branch_id" class="form-control" required>
                        <option value="">--Select Branch--</option>
                    </select>

                    @error('branch_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- BATCH CHECKBOXES --}}
                <div class="col-md-12 form-group mb-3">

                    <div class="selection-header">
                        <label class="mb-0">
                            Select Batches <span class="text-danger">*</span>
                        </label>

                        <span class="selection-count" id="batch_count">
                            0 Selected
                        </span>
                    </div>

                    <div class="batch-wrapper">

                        <div id="batch_checkbox_container">
                            <div class="text-muted">
                                Select Course to load batches...
                            </div>
                        </div>

                    </div>

                    @error('batch_ids')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    @error('batch_ids.*')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                </div>

                {{-- SEMESTER --}}
                <div class="col-md-4 form-group mb-2"
                    id="semester_container"
                    style="display:none;">

                    <label for="semester_id">Select Semester</label>

                    <select name="semester_id" id="semester_id" class="form-control">
                        <option value="">--Select Semester--</option>
                    </select>

                    @error('semester_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ANNUAL SEMESTER --}}
                <div class="col-md-4 form-group mb-2"
                    id="annual_semester_container"
                    style="display:none;">

                    <label for="annual_semester_id">Select Annual Terms</label>

                    <select name="annual_semester_id" id="annual_semester_id" class="form-control">
                        <option value="">--Select Annual Terms--</option>
                    </select>

                    @error('annual_semester_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- TEACHER --}}
                <div class="col-md-4 form-group mb-2">

                    <label for="teacher_id">
                        Teacher <span class="text-danger">*</span>
                    </label>

                    <select name="teacher_id" id="teacher_id" class="form-control" required>
                        <option value="">--Select Teacher--</option>

                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}">
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('teacher_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                </div>

                {{-- SUBJECT CHECKBOXES --}}
                <div class="col-md-12 form-group mb-2">

                    <div class="selection-header">
                        <label class="mb-0">
                            Select Subjects <span class="text-danger">*</span>
                        </label>

                        <span class="selection-count" id="subject_count">
                            0 Selected
                        </span>
                    </div>

                    <div class="subject-wrapper">

                        <div id="subject_checkbox_container">
                            <div class="text-muted">
                                Select filters to load subjects...
                            </div>
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
                Assign Subjects
            </button>
        </div>

    </form>

</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>

<script>
$(document).ready(function () {

    /* =========================
       RESET SEMESTER UI
    ========================== */

    function resetSemesterUI() {

        $('#semester_container').hide();
        $('#annual_semester_container').hide();

        $('#semester_id').html(
            '<option value="">--Select Semester--</option>'
        );

        $('#annual_semester_id').html(
            '<option value="">--Select Annual Terms--</option>'
        );
    }


    /* =========================
       UPDATE BATCH COUNT
    ========================== */

    function updateBatchCount() {

        let count = $('input[name="batch_ids[]"]:checked').length;

        $('#batch_count').text(count + ' Selected');
    }


    /* =========================
       UPDATE SUBJECT COUNT
    ========================== */

    function updateSubjectCount() {

        let count = $('input[name="subject_ids[]"]:checked').length;

        $('#subject_count').text(count + ' Selected');
    }


    /* =========================
       LOAD SUBJECTS
    ========================== */

    function loadSubjects() {

        let courseType = $('#course_type').val();
        let courseId = $('#course_id').val();
        let schemeId = $('#scheme_id').val();
        let branchId = $('#branch_id').val();
        let semesterId = $('#semester_id').val();
        let annualSemesterId = $('#annual_semester_id').val();

        // Batch selection is NOT required for subject loading
        if (!courseId || !schemeId || !branchId) {

            $('#subject_checkbox_container').html(
                '<div class="text-muted">Select Course, Scheme and Branch to load subjects...</div>'
            );

            updateSubjectCount();

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
                semester_id: semesterId,
                annual_semester_id: annualSemesterId
            },

            success: function (subjects) {

                let html = '';

                if (subjects.length > 0) {

                    html += '<div class="subject-grid">';

                    $.each(subjects, function (_, subject) {

                        html += `
                            <label class="subject-item">
                                <input
                                    type="checkbox"
                                    name="subject_ids[]"
                                    value="${subject.id}"
                                    class="subject-checkbox"
                                >

                                ${subject.name}
                                (<span class="text-primary">${subject.subject_code ?? ''}</span>)
                            </label>
                        `;

                    });

                    html += '</div>';

                } else {

                    html = '<div class="text-danger">No Subject Found</div>';

                }

                $('#subject_checkbox_container').html(html);

                updateSubjectCount();

            },

            error: function () {

                $('#subject_checkbox_container').html(
                    '<div class="text-danger">Unable to load subjects. Please try again.</div>'
                );

            }

        });

    }


    /* =========================
       LOAD BATCH CHECKBOXES
    ========================== */

    function loadBatchCheckboxes(batches) {

        let html = '';

        if (batches.length > 0) {

            html += '<div class="batch-grid">';

            $.each(batches, function (_, batch) {

                html += `
                    <label class="batch-item">
                        <input
                            type="checkbox"
                            name="batch_ids[]"
                            value="${batch.id}"
                            class="batch-checkbox"
                        >

                        ${batch.name}
                    </label>
                `;

            });

            html += '</div>';

        } else {

            html = '<div class="text-danger">No Batch Found</div>';

        }

        $('#batch_checkbox_container').html(html);

        updateBatchCount();

    }


    /* =========================
       BATCH CHECKBOX CHANGE
    ========================== */

    $(document).on('change', '.batch-checkbox', function () {

        updateBatchCount();

    });


    /* =========================
       SUBJECT CHECKBOX CHANGE
    ========================== */

    $(document).on('change', '.subject-checkbox', function () {

        updateSubjectCount();

    });


    /* =========================
       FILTER CHANGE
    ========================== */

    $('#branch_id, #scheme_id, #semester_id, #annual_semester_id')
        .on('change', function () {

            loadSubjects();

        });


    /* =========================
       LOAD COURSE DATA
    ========================== */

    function loadCourseData(courseId) {

        if (!courseId) {

            resetSemesterUI();

            $('#branch_id').html(
                '<option value="">--Select Branch--</option>'
            );

            $('#scheme_id').html(
                '<option value="">--Select Scheme--</option>'
            );

            $('#batch_checkbox_container').html(
                '<div class="text-muted">Select Course to load batches...</div>'
            );

            $('#subject_checkbox_container').html(
                '<div class="text-muted">Select filters to load subjects...</div>'
            );

            updateBatchCount();
            updateSubjectCount();

            return;
        }

        resetSemesterUI();

        $('#branch_id').html(
            '<option value="">Loading...</option>'
        );

        $('#scheme_id').html(
            '<option value="">Loading...</option>'
        );

        $('#batch_checkbox_container').html(
            '<div class="text-muted">Loading batches...</div>'
        );

        $('#subject_checkbox_container').html(
            '<div class="text-muted">Select filters to load subjects...</div>'
        );

        updateBatchCount();
        updateSubjectCount();


        /* =========================
           FETCH BRANCHES
        ========================== */

        $.get("{{ url('institute/branches') }}/" + courseId, function (branches) {

            $('#branch_id').html(
                '<option value="">--Select Branch--</option>'
            );

            $.each(branches, function (_, branch) {

                $('#branch_id').append(
                    `<option value="${branch.id}">${branch.name}</option>`
                );

            });

        });


        /* =========================
           FETCH COURSE DETAILS
        ========================== */

        $.get("{{ url('courses') }}/" + courseId + "/details", function (response) {


            /* ---------- SCHEMES ---------- */

            $('#scheme_id').html(
                '<option value="">--Select Scheme--</option>'
            );

            $.each(response.schemes, function (_, scheme) {

                $('#scheme_id').append(
                    `<option value="${scheme.id}">${scheme.name}</option>`
                );

            });


            /* ---------- BATCH CHECKBOXES ---------- */

            loadBatchCheckboxes(response.batches || []);


            /* ---------- COURSE TYPE ---------- */

            let courseType = $('#course_type').val();


            /* ---------- SEMESTER BASED ---------- */

            if (courseType === 'Semester Based') {

                $('#semester_container').show();

                $('#semester_id').html(
                    '<option value="">--Select Semester--</option>'
                );

                $.each(response.semesters, function (_, semester) {

                    $('#semester_id').append(
                        `<option value="${semester.id}">${semester.name}</option>`
                    );

                });

            }


            /* ---------- ANNUAL BASED ---------- */

            if (courseType === 'Annual Based') {

                $('#annual_semester_container').show();

                $('#annual_semester_id').html(
                    '<option value="">--Select Annual Terms--</option>'
                );

                $.each(response.annual_semesters, function (_, annual) {

                    $('#annual_semester_id').append(
                        `<option value="${annual.id}">${annual.name}</option>`
                    );

                });

            }

        });

    }


    /* =========================
       COURSE CHANGE
    ========================== */

    $('#course_id').on('change', function () {

        loadCourseData($(this).val());

    });


    /* =========================
       COURSE TYPE CHANGE
    ========================== */

    $('#course_type').on('change', function () {

        resetSemesterUI();

        let courseId = $('#course_id').val();

        if (courseId) {

            loadCourseData(courseId);

        }

    });


    /* =========================
       FORM VALIDATION
    ========================== */

    $('form').on('submit', function (e) {

        let batchCount = $('input[name="batch_ids[]"]:checked').length;

        let subjectCount = $('input[name="subject_ids[]"]:checked').length;

        if (batchCount === 0) {

            e.preventDefault();

            alert('Please select at least one Batch.');

            return false;
        }

        if (subjectCount === 0) {

            e.preventDefault();

            alert('Please select at least one Subject.');

            return false;
        }

    });


    /* =========================
       AUTO LOAD ON PAGE LOAD
    ========================== */

    let defaultCourse = $('#course_id').val();
    let defaultType = $('#course_type').val();

    if (defaultCourse && defaultType) {

        loadCourseData(defaultCourse);

    }

});
</script>

@endsection
