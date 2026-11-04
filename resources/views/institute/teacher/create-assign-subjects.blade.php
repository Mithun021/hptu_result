@extends('institute.partial.master')
@section('title', 'Assign Subjects')
@section('sub_title', 'Assign Subjects to Teachers')
@section('institute-content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title m-0">Create of Assign Subjects to Teachers</h4>
        <a href="{{ route('assign.subject') }}" class="btn btn-sm btn-danger">Back</a>
    </div>
    <form action="{{ route('save.assign.subject') }}" method="post"> @csrf
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 form-group mb-2">
                <label for="course_type">Course Type<span class="text-danger">*</span></label>
                <select name="course_type" id="course_type" class="form-control">
                    <option value="">--Select--</option>
                    <option value="Annual Based">Annual Based</option>
                    <option value="Semester Based">Semester Based</option>
                </select>
                @error('course_type') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="course_id">Course<span class="text-danger">*</span></label>
                    <select name="course_id" id="course_id" class="form-control">
                        <option value="">Select Course</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4 form-group mb-2">
                <label for="branch_id">Select Branch<span class="text-danger">*</span></label>
                <select name="branch_id" id="branch_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-4 form-group mb-2" id="semester_container"  style="display: none;">
                <label for="semester_id">Select Semester</label>
                <select name="semester_id" id="semester_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-4 form-group mb-2" id="annual_semester_container"  style="display: none;">
                <label for="annual_semester_id">Select Annual Terms</label>
                <select name="annual_semester_id" id="annual_semester_id" class="form-control">
                    <option value="">--Select--</option>
                </select>
            </div>
            <div class="col-md-4 form-group mb-2" id="subject_container">
                <label for="subject_id">Teacher<span class="text-danger">*</span></label>
                <select name="teacher_id" id="teacher_id" class="form-control">
                    <option value="">--Select Teacher--</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 form-group mb-2">
                <label>Select Subjects <span class="text-danger">*</span></label>

                <div id="subject_checkbox_container" class="border rounded p-3" 
                    style="max-height:250px; overflow-y:auto;">
                    
                    <div class="text-muted">Select filters to load subjects...</div>

                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <button type="submit" class="btn btn-primary">Assign Subjects</button>
    </form>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script> 
<script>
    $(document).ready(function () {

        function resetSemesterUI() {
            $('#semester_container').hide();
            $('#annual_semester_container').hide();
            $('#semester_id').html('<option value="">--Select Semester--</option>');
            $('#annual_semester_id').html('<option value="">--Select Annual Terms--</option>');
        }

        function loadSubjects() {

            let courseType = $('#course_type').val();
            let courseId   = $('#course_id').val();
            let branchId   = $('#branch_id').val();
            let semesterId = $('#semester_id').val();
            let annualSemesterId = $('#annual_semester_id').val();

            if (!courseId || !branchId) return;

            $('#subject_checkbox_container').html('Loading...');

            $.ajax({
                url: "{{ url('subjects/filter') }}",
                type: "GET",
                data: {
                    course_type: courseType,
                    course_id: courseId,
                    branch_id: branchId,
                    semester_id: semesterId,
                    annual_semester_id: annualSemesterId
                },
                success: function (subjects) {

                    let html = '';

                    if (subjects.length > 0) {

                        $.each(subjects, function (_, subject) {

                            html += `
                                <div class="form-check mb-1">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        name="subject_ids[]"
                                        value="${subject.id}"
                                        id="subject_${subject.id}">

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
                }
            });
        }

        $('#branch_id, #scheme_id, #semester_id, #annual_semester_id').on('change', function () {
            loadSubjects();
        });

        function loadCourseData(courseId) {
            if (!courseId) return;

            resetSemesterUI();

            $('#branch_id').html('<option>Please wait...</option>');
            $('#scheme_id').html('<option>Please wait...</option>');

            /* =========================
            FETCH BRANCHES
            ==========================*/
            $.get("{{ url('institute/branches') }}/" + courseId, function (branches) {
                $('#branch_id').html('<option value="">--Select Branch--</option>');
                $.each(branches, function (_, branch) {
                    $('#branch_id').append(
                        `<option value="${branch.id}">${branch.name}</option>`
                    );
                });
            });

            /* =========================
            FETCH COURSE DETAILS
            ==========================*/
            $.get("{{ url('courses') }}/" + courseId + "/details", function (response) {

                /* ---------- Schemes ---------- */
                $('#scheme_id').html('<option value="">--Select Scheme--</option>');
                $.each(response.schemes, function (_, scheme) {
                    $('#scheme_id').append(
                        `<option value="${scheme.id}">${scheme.name}</option>`
                    );
                });

                /* ---------- Bathes ---------- */
                $('#batch_id').html('<option value="">--Select Batch--</option>');
                $.each(response.batches, function (_, batch) {
                    $('#batch_id').append(
                        `<option value="${batch.id}">${batch.name}</option>`
                    );
                });

                let courseType = $('#course_type').val();

                /* ---------- Semester Based ---------- */
                if (courseType === 'Semester Based') {
                    $('#semester_container').show();
                    $('#semester_id').html('<option value="">--Select Semester--</option>');

                    $.each(response.semesters, function (_, semester) {
                        $('#semester_id').append(
                            `<option value="${semester.id}">${semester.name}</option>`
                        );
                    });
                }

                /* ---------- Annual Based ---------- */
                if (courseType === 'Annual Based') {
                    $('#annual_semester_container').show();
                    $('#annual_semester_id').html('<option value="">--Select Annual Terms--</option>');

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
        ==========================*/
        $('#course_id').on('change', function () {
            loadCourseData($(this).val());
        });

        /* =========================
        COURSE TYPE CHANGE
        ==========================*/
        $('#course_type').on('change', function () {
            resetSemesterUI();
            let courseId = $('#course_id').val();
            if (courseId) {
                loadCourseData(courseId);
            }
        });

        /* =========================
        AUTO LOAD ON PAGE LOAD (EDIT / DEFAULT VALUES)
        ==========================*/
        let defaultCourse = $('#course_id').val();
        let defaultType   = $('#course_type').val();

        if (defaultCourse && defaultType) {
            loadCourseData(defaultCourse);
        }

    });
</script>

@endsection