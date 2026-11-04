
<script>

$(document).ready(function () {

    let oldSchemeId          = "{{ old('scheme_id') }}";
    let oldBranchId          = "{{ old('branch_id') }}";
    let oldBatchId           = "{{ old('batch_id') }}";
    let oldSemesterId        = "{{ old('semester_id') }}";
    let oldAnnualSemesterId  = "{{ old('annual_semester_id') }}";

    function resetSemesterUI() {

        $('#semester_container').hide();
        $('#annual_semester_container').hide();

        $('#semester_id').html('<option value="">--Select Semester--</option>');
        $('#annual_semester_id').html('<option value="">--Select--</option>');
    }

    /* function loadSubjects() {

        let courseType = $('#course_type').val();
        let courseId   = $('#course_id').val();
        let schemeId   = $('#scheme_id').val();
        let branchId   = $('#branch_id').val();
        let semesterId = $('#semester_id').val();
        let annualSemesterId = $('#annual_semester_id').val();

        if (!courseId || !branchId) {
            return;
        }

        $('#subject_id').html('<option>Please wait...</option>');

        $.ajax({
            url: "{{ url('result/subjects/filter') }}",
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

                $('#subject_id').html('<option value="">--Select Subject--</option>');

                if (subjects.length > 0) {
                    $.each(subjects, function (_, subject) {
                        $('#subject_id').append(
                            `<option value="${subject.id}">${subject.name}</option>`
                        );
                    });
                } else {
                    $('#subject_id').append(
                        `<option value="">No Subject Found</option>`
                    );
                }
            }
        });
    }

    $('#branch_id, #scheme_id, #semester_id, #annual_semester_id').on('change', function () {
        loadSubjects();
    }); */

    function loadCourseData(courseId) {

        if (!courseId) return;

        resetSemesterUI();

        $('#branch_id').html('<option>Please wait...</option>');
        $('#scheme_id').html('<option>Please wait...</option>');
        $('#batch_id').html('<option>Please wait...</option>');

        /* =========================
           FETCH BRANCHES
        ==========================*/

        $.get("{{ url('branches') }}/" + courseId, function (branches) {

            $('#branch_id').html('<option value="">--Select Branch--</option>');

            $.each(branches, function (_, branch) {

                let selected = oldBranchId == branch.id ? 'selected' : '';

                $('#branch_id').append(
                    `<option value="${branch.id}" ${selected}>
                        ${branch.name}
                    </option>`
                );
            });

        });

        /* =========================
           FETCH COURSE DETAILS
        ==========================*/

        $.get("{{ url('courses') }}/" + courseId + "/details", function (response) {

            /* ---------- SCHEMES ---------- */

            $('#scheme_id').html('<option value="">--Select Scheme--</option>');

            $.each(response.schemes, function (_, scheme) {

                let selected = oldSchemeId == scheme.id ? 'selected' : '';

                $('#scheme_id').append(
                    `<option value="${scheme.id}" ${selected}>
                        ${scheme.name}
                    </option>`
                );
            });

            /* ---------- BATCH ---------- */

            $('#batch_id').html('<option value="">--Select Batch--</option>');

            $.each(response.batches, function (_, batch) {

                let selected = oldBatchId == batch.id ? 'selected' : '';

                $('#batch_id').append(
                    `<option value="${batch.id}" ${selected}>
                        ${batch.name}
                    </option>`
                );
            });

            let courseType = $('#course_type').val();

            /* =========================
               SEMESTER BASED
            ==========================*/

            if (courseType === 'Semester Based') {

                $('#semester_container').show();
                $('#annual_semester_container').hide();

                $('#semester_id').html('<option value="">--Select Semester--</option>');

                $.each(response.semesters, function (_, semester) {

                    let selected = oldSemesterId == semester.id ? 'selected' : '';

                    $('#semester_id').append(
                        `<option value="${semester.id}" ${selected}>
                            ${semester.name}
                        </option>`
                    );
                });
            }

            /* =========================
               ANNUAL BASED
            ==========================*/

            if (courseType === 'Annual Based') {

                $('#annual_semester_container').show();
                $('#semester_container').hide();

                $('#annual_semester_id').html('<option value="">--Select--</option>');

                $.each(response.annual_semesters, function (_, annual) {

                    let selected = oldAnnualSemesterId == annual.id ? 'selected' : '';

                    $('#annual_semester_id').append(
                        `<option value="${annual.id}" ${selected}>
                            ${annual.name}
                        </option>`
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
       AUTO LOAD OLD VALUES
    ==========================*/

    let defaultCourse = $('#course_id').val();
    let defaultType   = $('#course_type').val();

    if (defaultCourse && defaultType) {
        loadCourseData(defaultCourse);
    }

});

$('#graceMarkForm').on('submit', function (e) {

    e.preventDefault();

    let form = this;
    let submitBtn = $('#graceSubmitBtn');

    // Disable button immediately
    submitBtn.prop('disabled', true);

    // Change button text
    submitBtn.html('Please wait...');

    $.ajax({
        url: $(form).attr('action'),
        type: 'POST',
        data: new FormData(form),
        processData: false,
        contentType: false,

        beforeSend: function () {

            submitBtn.prop('disabled', true);
            submitBtn.html('Please wait...');

        },

        success: function (response) {

            if (response.success) {

                // Toastify success
                Toastify({
                    text: response.message || 'Form saved successfully.',
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    close: true,
                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                    stopOnFocus: true
                }).showToast();
                
                submitBtn.prop('disabled', false);
                submitBtn.html('Submit');

                // Reload after successful save
                // setTimeout(function () {
                //     location.reload();
                // }, 1000);

            } else {

                Toastify({
                    text: response.message || 'Form has not been saved.',
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    close: true,
                    backgroundColor: "linear-gradient(to right, #ff416c, #ff4b2b)",
                    stopOnFocus: true
                }).showToast();

                // Enable button again
                submitBtn.prop('disabled', false);
                submitBtn.html('Submit');
            }
        },

        error: function (xhr) {

            // Laravel validation errors
            if (xhr.status === 422 && xhr.responseJSON?.errors) {

                $.each(xhr.responseJSON.errors, function (field, messages) {

                    $.each(messages, function (_, message) {

                        Toastify({
                            text: message,
                            duration: 5000,
                            gravity: "top",
                            position: "right",
                            close: true,
                            backgroundColor: "linear-gradient(to right, #ff416c, #ff4b2b)",
                            stopOnFocus: true
                        }).showToast();

                    });

                });

            } else {

                Toastify({
                    text: xhr.responseJSON?.message || 'Something went wrong. Form has not been saved.',
                    duration: 5000,
                    gravity: "top",
                    position: "right",
                    close: true,
                    backgroundColor: "linear-gradient(to right, #ff416c, #ff4b2b)",
                    stopOnFocus: true
                }).showToast();

            }

            // Form save nahi hua
            // Isliye button dobara enable
            submitBtn.prop('disabled', false);
            submitBtn.html('Submit');
        }
    });

});

</script>
