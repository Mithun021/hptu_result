<script>
$(document).ready(function () {

    function resetSemesterUI() {
        $('#semester_container').hide();
        $('#annual_semester_container').hide();

        $('#semester_id').html('<option value="">--Select Semester--</option>');
        $('#annual_semester_id').html('<option value="">--Select--</option>');
    }

    function loadCourseData(courseId) {
        if (!courseId) return;

        resetSemesterUI();

        /* =========================
           LOADERS
        ==========================*/
        $('#branch_id').html('<option>Please wait...</option>');
        $('#scheme_id').html('<option>Please wait...</option>');
        $('#batch_id').html('<option>Please wait...</option>');
        $('#institute_id').html('<option>Please wait...</option>');

        /* =========================
           FETCH BRANCHES
        ==========================*/
        $.get("{{ url('branches') }}/" + courseId, function (branches) {
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

            /* ---------- Batches ---------- */
            $('#batch_id').html('<option value="">--Select Batch--</option>');
            $.each(response.batches, function (_, batch) {
                $('#batch_id').append(
                    `<option value="${batch.id}">${batch.name}</option>`
                );
            });

            /* ---------- Institutes ---------- */
            $('#institute_id').html('<option value="">--Select Institute--</option>');
            $.each(response.institutes, function (_, institute) {
                $('#institute_id').append(
                    `<option value="${institute.id}">${institute.name}</option>`
                );
            });

            let courseType = $('#course_type').val();

            /* =========================
               SEMESTER BASED
            ==========================*/
            if (courseType === 'Semester Based') {
                $('#semester_container').show();
                $('#annual_semester_container').hide();

                $('#semester_id').html('');
                $.each(response.semesters, function (_, semester) {
                    $('#semester_id').append(`
                        <div class="form-check">
                            <input class="form-check-input semester_checkbox" type="checkbox" name="semester_id[]" value="${semester.id}">
                            <label class="form-check-label">${semester.name}</label>
                        </div>
                    `);
                });
            }

           /* =========================
            ANNUAL BASED (CHECKBOX)
            ==========================*/
            if (courseType === 'Annual Based') {
                $('#annual_semester_container').show();
                $('#semester_container').hide();

                $('#annual_semester_id').html('');

                $.each(response.annual_semesters, function (_, annual) {
                    $('#annual_semester_id').append(`
                        <div class="form-check">
                            <input 
                                class="form-check-input annual_checkbox" 
                                type="checkbox" 
                                name="annual_semester_id[]" 
                                value="${annual.id}"
                            >
                            <label class="form-check-label">
                                ${annual.name}
                            </label>
                        </div>
                    `);
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
       AUTO LOAD (EDIT / OLD)
    ==========================*/
    let defaultCourse = $('#course_id').val();
    let defaultType   = $('#course_type').val();

    if (defaultCourse && defaultType) {
        loadCourseData(defaultCourse);
    }

});
</script>