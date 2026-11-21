
<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

<script>
$(document).ready(function () {

    // =========================
    // MODAL CLOSE
    // =========================
    $(document).on('click', '.downloadSampleButton', function () {

        let modalEl = document.getElementById('importMarksModal');
        let modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        modal.hide();
    });


    // =========================
    // MARKS TYPE CHANGE
    // =========================
    function updateMarksType() {

        let courseType = $('#export_course_type').val();
        let selectedOption = $('#export_course_id option:selected');
        let courseCategory = selectedOption.data('course');

        let marksType = $('#export_marks_type');

        marksType.html('<option value="">--Select--</option>');

        if (!courseType || !courseCategory) return;

        if (courseType === 'Semester Based') {

            if (courseCategory === 'UG' || courseCategory === 'Diploma') {

                marksType.append(`
                    <option value="Internal">Internal</option>
                `);

            }
        }

        if (courseType === 'Annual Based') {

            if (courseCategory === 'UG' || courseCategory === 'Diploma') {

                marksType.append(`
                    <option value="Internal 1">Internal 1</option>
                    <option value="Internal 2">Internal 2</option>
                    <option value="Internal 3">Internal 3</option>
                `);

            }
        }
    }

    $('#export_course_type, #export_course_id').on('change', updateMarksType);


    // =========================
    // RESET SEMESTER UI
    // =========================
    function resetSemesterUI() {

        $('#export_semester_container').hide();
        $('#export_annual_semester_container').hide();

        $('#export_semester_id').html(
            '<option value="">--Select Semester--</option>'
        );

        $('#export_annual_semester_id').html(
            '<option value="">--Select Annual Semester--</option>'
        );

        $('#export_subject_id').html(
            '<option value="">--Select Subject--</option>'
        );
    }


    // =========================
    // LOAD SUBJECTS
    // =========================
    function loadSubjects() {

        let courseType = $('#export_course_type').val();
        let courseId = $('#export_course_id').val();
        let schemeId = $('#export_scheme_id').val();
        let branchId = $('#export_branch_id').val();
        let batchId = $('#export_batch_id').val();
        let semesterId = $('#export_semester_id').val();
        let annualSemesterId = $('#export_annual_semester_id').val();

        // Required filters
        if (!courseId || !branchId || !batchId) {

            $('#export_subject_id').html(
                '<option value="">--Select Subject--</option>'
            );

            return;
        }

        if (courseType === 'Semester Based' && !semesterId) {

            $('#export_subject_id').html(
                '<option value="">--Select Subject--</option>'
            );

            return;
        }

        if (courseType === 'Annual Based' && !annualSemesterId) {

            $('#export_subject_id').html(
                '<option value="">--Select Subject--</option>'
            );

            return;
        }

        $('#export_subject_id').html(
            '<option value="">Please wait...</option>'
        );

        $.ajax({

            url: "{{ url('institute/subjects/filter') }}",

            type: "GET",

            dataType: "json",

            data: {
                course_type: courseType,
                course_id: courseId,
                scheme_id: schemeId,
                branch_id: branchId,
                batch_id: batchId,
                semester_id: semesterId,
                annual_semester_id: annualSemesterId
            },

            success: function (response) {

                $('#export_subject_id').empty();

                // =========================
                // SERVER RESPONSE CHECK
                // =========================
                if (!response.success) {

                    $('#export_subject_id').append(
                        $('<option>', {
                            value: '',
                            text: response.message || 'Unable to load subjects'
                        })
                    );

                    console.error('Subject API Error:', response);

                    return;
                }

                let subjects = response.data || [];

                $('#export_subject_id').append(
                    $('<option>', {
                        value: '',
                        text: '--Select Subject--'
                    })
                );

                // =========================
                // SUBJECTS FOUND
                // =========================
                if (subjects.length > 0) {

                    $.each(subjects, function (index, subject) {

                        $('#export_subject_id').append(
                            $('<option>', {
                                value: subject.id,
                                text: subject.name +
                                    (subject.subject_code
                                        ? ' (' + subject.subject_code + ')'
                                        : '')
                            })
                        );

                    });

                    console.log('Subjects Loaded:', subjects);

                } else {

                    $('#export_subject_id').empty().append(
                        $('<option>', {
                            value: '',
                            text: 'No Subject Found'
                        })
                    );

                    console.warn('No Subject Found. Debug Details:', response.debug);

                    console.table(response.debug ? {
                        login_type: response.debug.login_type,
                        teacher_id: response.debug.teacher_id,
                        matching_assignments: response.debug.matching_assignments,
                        assignments_with_selected_batch: response.debug.assignments_with_selected_batch,
                        assigned_subject_ids: JSON.stringify(response.debug.assigned_subject_ids),
                        subjects_before_teacher_assignment_filter: response.debug.subjects_before_teacher_assignment_filter,
                        final_subject_count: response.debug.final_subject_count
                    } : {});

                }

            },

            // =========================
            // AJAX ERROR
            // =========================
            error: function (xhr, status, error) {

                $('#export_subject_id').empty();

                let message = 'Failed to load subjects.';

                let response = xhr.responseJSON;

                if (response) {

                    message = response.message || message;

                    // Validation errors
                    if (response.errors) {

                        let validationMessages = [];

                        $.each(response.errors, function (field, errors) {

                            validationMessages.push(
                                field + ': ' + errors.join(', ')
                            );

                        });

                        message += ' ' + validationMessages.join(' | ');
                    }

                    if (response.error) {
                        message += ' ' + response.error;
                    }

                } else if (xhr.responseText) {

                    message += ' Server Response: ' +
                        xhr.responseText.substring(0, 300);

                }

                $('#export_subject_id').append(
                    $('<option>', {
                        value: '',
                        text: message.substring(0, 180)
                    })
                );

                console.error('Subject AJAX Error:', {
                    status: xhr.status,
                    ajaxStatus: status,
                    error: error,
                    response: response,
                    responseText: xhr.responseText
                });

            }

        });
    }


    // =========================
    // SUBJECT FILTER CHANGE
    // =========================
    $(
        '#export_branch_id, #export_scheme_id, #export_batch_id, #export_semester_id, #export_annual_semester_id'
    ).on('change', function () {

        loadSubjects();

    });


    // =========================
    // LOAD COURSE DATA
    // =========================
    function loadCourseData(courseId) {

        if (!courseId) return;

        resetSemesterUI();

        $('#export_branch_id').html(
            '<option value="">Please wait...</option>'
        );

        $('#export_scheme_id').html(
            '<option value="">Please wait...</option>'
        );

        $('#export_batch_id').html(
            '<option value="">--Select Batch--</option>'
        );


        // =========================
        // LOAD BRANCHES
        // =========================
        $.get(
            "{{ url('institute/branches') }}/" + courseId,
            function (branches) {

                $('#export_branch_id').html(
                    '<option value="">--Select Branch--</option>'
                );

                $.each(branches, function (_, branch) {

                    $('#export_branch_id').append(
                        `<option value="${branch.id}">${branch.name}</option>`
                    );

                });

            }
        );


        // =========================
        // LOAD COURSE DETAILS
        // =========================
        $.get(
            "{{ url('courses') }}/" + courseId + "/details",
            function (response) {

                // Schemes
                $('#export_scheme_id').html(
                    '<option value="">--Select Scheme--</option>'
                );

                $.each(response.schemes, function (_, scheme) {

                    $('#export_scheme_id').append(
                        `<option value="${scheme.id}">${scheme.name}</option>`
                    );

                });


                // Batches
                $('#export_batch_id').html(
                    '<option value="">--Select Batch--</option>'
                );

                $.each(response.batches, function (_, batch) {

                    $('#export_batch_id').append(
                        `<option value="${batch.id}">${batch.name}</option>`
                    );

                });


                let courseType = $('#export_course_type').val();


                // =========================
                // SEMESTER BASED
                // =========================
                if (courseType === 'Semester Based') {

                    $('#export_semester_container').show();

                    $('#export_semester_id').html(
                        '<option value="">--Select Semester--</option>'
                    );

                    $.each(response.semesters, function (_, semester) {

                        $('#export_semester_id').append(
                            `<option value="${semester.id}">${semester.name}</option>`
                        );

                    });

                }


                // =========================
                // ANNUAL BASED
                // =========================
                if (courseType === 'Annual Based') {

                    $('#export_annual_semester_container').show();

                    $('#export_annual_semester_id').html(
                        '<option value="">--Select Annual Terms--</option>'
                    );

                    $.each(response.annual_semesters, function (_, annual) {

                        $('#export_annual_semester_id').append(
                            `<option value="${annual.id}">${annual.name}</option>`
                        );

                    });

                }

            }
        );

    }


    // =========================
    // COURSE CHANGE
    // =========================
    $('#export_course_id').on('change', function () {

        loadCourseData($(this).val());

        updateMarksType();

    });


    // =========================
    // COURSE TYPE CHANGE
    // =========================
    $('#export_course_type').on('change', function () {

        resetSemesterUI();

        updateMarksType();

        let courseId = $('#export_course_id').val();

        if (courseId) {

            loadCourseData(courseId);

        }

    });


    // =========================
    // INITIAL PAGE LOAD
    // =========================
    let defaultCourse = $('#export_course_id').val();
    let defaultType = $('#export_course_type').val();

    updateMarksType();

    if (defaultCourse && defaultType) {

        loadCourseData(defaultCourse);

    }

});
</script>
