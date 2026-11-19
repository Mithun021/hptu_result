
@extends('institute.partial.master')

@section('title', 'Edit Assign Subjects')
@section('sub_title', 'Edit Assign Subjects To Teacher')

@section('institute-content')

<style>
    .assignment-wrapper {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 5px;
    }

    .assignment-section {
        border: 1px solid #e5e7eb;
        border-radius: 5px;
        padding: 15px;
        background: #fff;
        max-height: 250px;
        overflow-y: auto;
    }

    .assignment-section .form-check {
        padding: 7px 10px 7px 30px;
        border-bottom: 1px solid #f1f1f1;
        margin-bottom: 0;
    }

    .assignment-section .form-check:last-child {
        border-bottom: none;
    }

    .assignment-section .form-check:hover {
        background: #f8f9fa;
    }

    .assignment-section .form-check-input {
        cursor: pointer;
    }

    .assignment-section .form-check-label {
        cursor: pointer;
        width: 100%;
    }

    .assignment-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .assignment-count {
        font-size: 12px;
        color: #6c757d;
    }

    .required {
        color: red;
    }
</style>

<div class="card assignment-wrapper">

    {{-- ================= HEADER ================= --}}
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title m-0">Edit Assigned Subjects</h4>

        <a href="{{ route('assign.subject') }}" class="btn btn-sm btn-danger">
            Back
        </a>
    </div>

    {{-- ================= FORM ================= --}}
    <form action="{{ route('update.assign.subject', $assignment->id) }}"
          method="POST"
          id="editAssignmentForm">

        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="row">

                {{-- Course Type --}}
                <div class="col-md-4 form-group mb-3 d-none">
                    <label>Course Type</label>

                    <select name="course_type" id="course_type" class="form-control">
                        <option value="Semester Based" selected>Semester Based</option>
                    </select>
                </div>

                {{-- Course --}}
                <div class="col-md-4 form-group mb-3">
                    <label>
                        Select Course <span class="required">*</span>
                    </label>

                    <select name="course_id" id="course_id"
                            class="form-control" required>

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
                <div class="col-md-4 form-group mb-3">
                    <label>
                        Select Scheme <span class="required">*</span>
                    </label>

                    <select name="scheme_id" id="scheme_id"
                            class="form-control" required>

                        <option value="">--Select Scheme--</option>

                    </select>

                    @error('scheme_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Branch --}}
                <div class="col-md-4 form-group mb-3">
                    <label>
                        Select Branch <span class="required">*</span>
                    </label>

                    <select name="branch_id" id="branch_id"
                            class="form-control" required>

                        <option value="">--Select Branch--</option>

                    </select>

                    @error('branch_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Semester --}}
                <div class="col-md-4 form-group mb-3" id="semester_container">
                    <label>
                        Select Semester <span class="required">*</span>
                    </label>

                    <select name="semester_id" id="semester_id"
                            class="form-control" required>

                        <option value="">--Select Semester--</option>

                    </select>

                    @error('semester_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Teacher --}}
                <div class="col-md-4 form-group mb-3">
                    <label>Teacher</label>

                    <input type="text"
                           class="form-control"
                           value="{{ $teacher?->name ?? '-' }}"
                           readonly>
                </div>

                {{-- ================= BATCHES ================= --}}
                <div class="col-md-12 form-group mb-3">

                    <div class="assignment-label">
                        <label class="mb-0">
                            Select Batches <span class="required">*</span>
                        </label>

                        <span class="assignment-count" id="batch_count">
                            0 Selected
                        </span>
                    </div>

                    <div id="batch_checkbox_container"
                         class="assignment-section">

                        <div class="text-muted">
                            Loading batches...
                        </div>

                    </div>

                    @error('batch_ids')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    @error('batch_ids.*')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                </div>

                {{-- ================= SUBJECTS ================= --}}
                <div class="col-md-12 form-group mb-3">

                    <div class="assignment-label">
                        <label class="mb-0">
                            Select Subjects <span class="required">*</span>
                        </label>

                        <span class="assignment-count" id="subject_count">
                            0 Selected
                        </span>
                    </div>

                    <div id="subject_checkbox_container"
                         class="assignment-section">

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

        {{-- ================= FOOTER ================= --}}
        <div class="card-footer">

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Subjects
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

    /* ==========================================
       SELECTED VALUES
    ========================================== */

    let selectedScheme = @json((string) old('scheme_id', $selectedScheme));

    let selectedBranch = @json((string) old('branch_id', $selectedBranch));

    let selectedSemester = @json((string) old('semester_id', $selectedSemester));

    let selectedBatchIds = @json(
        old('batch_ids', $selectedBatchIds->values()->all())
    );

    let assignedSubjects = @json(
        old('subject_ids', $assignedSubjectIds->values()->all())
    );

    selectedBatchIds = selectedBatchIds.map(id => String(id));

    assignedSubjects = assignedSubjects.map(id => String(id));


    /* ==========================================
       UPDATE COUNTS
    ========================================== */

    function updateBatchCount() {

        let count = $('.batch-checkbox:checked').length;

        $('#batch_count').text(count + ' Selected');
    }

    function updateSubjectCount() {

        let count = $('.subject-checkbox:checked').length;

        $('#subject_count').text(count + ' Selected');
    }


    /* ==========================================
       LOAD SUBJECTS
       Batch is not required for subject loading
    ========================================== */

    function loadSubjects() {

        let courseType = $('#course_type').val();
        let courseId = $('#course_id').val();
        let schemeId = $('#scheme_id').val();
        let branchId = $('#branch_id').val();
        let semesterId = $('#semester_id').val();

        if (!courseId || !schemeId || !branchId || !semesterId) {

            $('#subject_checkbox_container').html(
                '<div class="text-muted">Select all filters to load subjects...</div>'
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
                annual_semester_id: ''
            },

            success: function (subjects) {

                let html = '';

                if (subjects && subjects.length > 0) {

                    $.each(subjects, function (_, subject) {

                        let isChecked = assignedSubjects.includes(
                            String(subject.id)
                        ) ? 'checked' : '';

                        html += `
                            <div class="form-check">

                                <input class="form-check-input subject-checkbox"
                                    type="checkbox"
                                    name="subject_ids[]"
                                    value="${subject.id}"
                                    id="subject_${subject.id}"
                                    ${isChecked}>

                                <label class="form-check-label"
                                    for="subject_${subject.id}">

                                    ${subject.name}

                                    <span class="text-primary">
                                        (${subject.subject_code ?? ''})
                                    </span>

                                </label>

                            </div>
                        `;
                    });

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


    /* ==========================================
       LOAD COURSE DATA
    ========================================== */

    function loadCourseData(courseId) {

        if (!courseId) {

            $('#scheme_id').html('<option value="">--Select Scheme--</option>');
            $('#branch_id').html('<option value="">--Select Branch--</option>');
            $('#semester_id').html('<option value="">--Select Semester--</option>');

            $('#batch_checkbox_container').html(
                '<div class="text-muted">Please select course first.</div>'
            );

            $('#subject_checkbox_container').html(
                '<div class="text-muted">Please select course first.</div>'
            );

            updateBatchCount();
            updateSubjectCount();

            return;
        }

        $('#scheme_id').html('<option value="">Loading...</option>');
        $('#branch_id').html('<option value="">Loading...</option>');
        $('#semester_id').html('<option value="">Loading...</option>');

        $('#batch_checkbox_container').html(
            '<div class="text-muted">Loading batches...</div>'
        );

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


            /* ---------- BRANCHES ---------- */

            $('#branch_id').html(
                '<option value="">--Select Branch--</option>'
            );

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


            /* ---------- SCHEMES ---------- */

            $('#scheme_id').html(
                '<option value="">--Select Scheme--</option>'
            );

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


            /* ---------- BATCHES ---------- */

            let batchHtml = '';

            $.each(courseDetails.batches || [], function (_, batch) {

                let selected = selectedBatchIds.includes(String(batch.id))
                    ? 'checked'
                    : '';

                batchHtml += `
                    <div class="form-check">

                        <input class="form-check-input batch-checkbox"
                            type="checkbox"
                            name="batch_ids[]"
                            value="${batch.id}"
                            id="batch_${batch.id}"
                            ${selected}>

                        <label class="form-check-label"
                            for="batch_${batch.id}">

                            ${batch.name}

                        </label>

                    </div>
                `;

            });

            if (batchHtml === '') {

                batchHtml = '<div class="text-danger">No Batch Found</div>';

            }

            $('#batch_checkbox_container').html(batchHtml);

            updateBatchCount();


            /* ---------- SEMESTERS ---------- */

            $('#semester_id').html(
                '<option value="">--Select Semester--</option>'
            );

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


            /* ---------- LOAD SUBJECTS ---------- */

            loadSubjects();

        }


        /* ---------- FETCH BRANCHES ---------- */

        $.get("{{ url('institute/branches') }}/" + courseId)

            .done(function (branches) {

                branchesData = branches || [];

                branchesLoaded = true;

                finishLoading();

            })

            .fail(function () {

                $('#branch_id').html(
                    '<option value="">Unable to load branches</option>'
                );

            });


        /* ---------- FETCH COURSE DETAILS ---------- */

        $.get("{{ url('courses') }}/" + courseId + "/details")

            .done(function (response) {

                courseDetails = response;

                detailsLoaded = true;

                finishLoading();

            })

            .fail(function () {

                $('#batch_checkbox_container').html(
                    '<div class="text-danger">Unable to load batches.</div>'
                );

                $('#subject_checkbox_container').html(
                    '<div class="text-danger">Unable to load course details.</div>'
                );

            });

    }


    /* ==========================================
       BATCH CHECKBOX CHANGE
    ========================================== */

    $(document).on('change', '.batch-checkbox', function () {

        updateBatchCount();

    });


    /* ==========================================
       SUBJECT CHECKBOX CHANGE
    ========================================== */

    $(document).on('change', '.subject-checkbox', function () {

        updateSubjectCount();

    });


    /* ==========================================
       COURSE CHANGE
    ========================================== */

    $('#course_id').on('change', function () {

        selectedScheme = '';
        selectedBranch = '';
        selectedSemester = '';

        selectedBatchIds = [];
        assignedSubjects = [];

        loadCourseData($(this).val());

    });


    /* ==========================================
       FILTER CHANGE
    ========================================== */

    $('#scheme_id, #branch_id, #semester_id').on('change', function () {

        selectedScheme = $('#scheme_id').val();
        selectedBranch = $('#branch_id').val();
        selectedSemester = $('#semester_id').val();

        // Clear old subject selection when academic filters change
        assignedSubjects = [];

        loadSubjects();

    });


    /* ==========================================
       FORM VALIDATION
    ========================================== */

    $('#editAssignmentForm').on('submit', function (e) {

        let selectedBatches = $('.batch-checkbox:checked').length;

        let selectedSubjectCount = $('.subject-checkbox:checked').length;

        if (selectedBatches === 0) {

            e.preventDefault();

            alert('Please select at least one batch.');

            $('#batch_checkbox_container').focus();

            return false;
        }

        if (selectedSubjectCount === 0) {

            e.preventDefault();

            alert('Please select at least one subject.');

            $('#subject_checkbox_container').focus();

            return false;
        }

    });


    /* ==========================================
       INITIAL PAGE LOAD
    ========================================== */

    let defaultCourse = $('#course_id').val();

    if (defaultCourse) {

        loadCourseData(defaultCourse);

    }

});
</script>

@endsection
