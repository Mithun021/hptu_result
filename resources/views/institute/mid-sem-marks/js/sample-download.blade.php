
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

            data: {
                course_type: courseType,
                course_id: courseId,
                scheme_id: schemeId,
                branch_id: branchId,
                batch_id: batchId,
                semester_id: semesterId,
                annual_semester_id: annualSemesterId
            },

            success: function (subjects) {

                $('#export_subject_id').html(
                    '<option value="">--Select Subject--</option>'
                );

                if (subjects.length > 0) {

                    $.each(subjects, function (_, subject) {

                        $('#export_subject_id').append(
                            $('<option>', {
                                value: subject.id,
                                text: subject.name
                            })
                        );

                    });

                } else {

                    $('#export_subject_id').html(
                        '<option value="">No Subject Found</option>'
                    );

                }

            },

            error: function (xhr) {

                console.log(xhr.responseText);

                $('#export_subject_id').html(
                    '<option value="">Failed to load subjects</option>'
                );

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
