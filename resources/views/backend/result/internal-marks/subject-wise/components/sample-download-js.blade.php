
<script>

$(document).ready(function () {

    // =========================
    // IMPORT MODAL HIDE + EXPORT OFFCANVAS SHOW
    // =========================
    $(document).on('click', '.downloadSampleButton', function () {

        let importModalElement = document.getElementById('importMarksModal');
        let exportOffcanvasElement = document.getElementById('export-internal-offcanvas');

        let importModal = bootstrap.Modal.getOrCreateInstance(importModalElement);
        let exportOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(exportOffcanvasElement);

        // Import modal hide hone ke baad offcanvas show hoga
        importModalElement.addEventListener('hidden.bs.modal', function () {
            exportOffcanvas.show();
        }, { once: true });

        importModal.hide();

    });


    // =========================
    // LOAD MID SEM EXAMS
    // =========================
    function loadMidSemExams(courseType, examDropdown) {

        examDropdown.html('<option value="">--Select--</option>');

        if (!courseType) {
            return;
        }

        $.ajax({
            url: "{{ route('coursebased.get.mid.sem.exams') }}",
            type: "GET",
            data: {
                course_type: courseType
            },
            dataType: "json",

            beforeSend: function () {
                examDropdown.html('<option value="">Loading...</option>');
            },

            success: function (response) {

                examDropdown.html('<option value="">--Select--</option>');

                if (response.status && response.data.length > 0) {

                    $.each(response.data, function (index, exam) {

                        examDropdown.append(
                            $('<option>', {
                                value: exam.id,
                                text: exam.mid_sem_exam_name
                            })
                        );

                    });

                } else {

                    examDropdown.html('<option value="">No Exam Found</option>');

                }

            },

            error: function (xhr) {

                console.log(xhr.responseText);

                examDropdown.html('<option value="">Failed to load exams</option>');

            }
        });

    }


    // =========================
    // INITIAL LOAD - MID SEM EXAMS
    // =========================
    if ($('#export-internal-course-type').length) {

        loadMidSemExams(
            $('#export-internal-course-type').val(),
            $('#export-internal-mid-sem-exam-id')
        );

    }


    // =========================
    // PREVENT ARROW UP/DOWN
    // =========================
    $(document).on('keydown', '.obtained-marks', function (e) {

        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            e.preventDefault();
        }

    });


    // =========================
    // MARKS TYPE CHANGE
    // =========================
    function updateMarksType() {

        let courseType = $('#export-internal-course-type').val();

        let selectedOption = $('#export-internal-course-id option:selected');

        let courseCategory = selectedOption.data('course');

        let marksType = $('#export-internal-marks-type');

        marksType.html('<option value="">--Select--</option>');

        if (!courseType || !courseCategory) return;


        // SEMESTER BASED
        if (courseType === 'Semester Based') {

            if (courseCategory === 'UG' || courseCategory === 'Diploma') {

                marksType.append(`
                    <option value="Internal">Internal</option>
                `);

            }

        }


        // ANNUAL BASED
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


    // =========================
    // MARKS TYPE EVENTS
    // =========================
    $('#export-internal-course-type, #export-internal-course-id').on('change', function () {

        updateMarksType();

    });


    // =========================
    // RESET SEMESTER UI
    // =========================
    function resetSemesterUI() {

        $('#export-internal-semester-container').hide();

        $('#export-internal-annual-semester-container').hide();

        $('#export-internal-semester-id').html(
            '<option value="">--Select Semester--</option>'
        );

        $('#export-internal-annual-semester-id').html(
            '<option value="">--Select Annual Semester--</option>'
        );

        $('#export-internal-subject-id').html(
            '<option value="">--Select Subject--</option>'
        );

    }


    // =========================
    // LOAD SUBJECTS
    // =========================
    function loadSubjects() {

        let courseType = $('#export-internal-course-type').val();

        let courseId = $('#export-internal-course-id').val();

        let schemeId = $('#export-internal-scheme-id').val();

        let branchId = $('#export-internal-branch-id').val();

        let semesterId = $('#export-internal-semester-id').val();

        let annualSemesterId = $('#export-internal-annual-semester-id').val();


        if (!courseId || !branchId) {
            return;
        }


        $('#export-internal-subject-id').html(
            '<option value="">Please wait...</option>'
        );


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

                $('#export-internal-subject-id').html(
                    '<option value="">--Select Subject--</option>'
                );

                if (subjects.length > 0) {

                    $.each(subjects, function (_, subject) {

                        $('#export-internal-subject-id').append(
                            $('<option>', {
                                value: subject.id,
                                text: subject.name
                            })
                        );

                    });

                } else {

                    $('#export-internal-subject-id').append(
                        '<option value="">No Subject Found</option>'
                    );

                }

            },

            error: function (xhr) {

                console.log(xhr.responseText);

                $('#export-internal-subject-id').html(
                    '<option value="">Failed to load subjects</option>'
                );

            }

        });

    }


    // =========================
    // SUBJECT FILTER CHANGE
    // =========================
    $(
        '#export-internal-branch-id, #export-internal-scheme-id, #export-internal-semester-id, #export-internal-annual-semester-id'
    ).on('change', function () {

        loadSubjects();

    });


    // =========================
    // LOAD COURSE DATA
    // =========================
    function loadCourseData(courseId) {

        if (!courseId) return;

        resetSemesterUI();


        // LOADERS
        $('#export-internal-branch-id').html(
            '<option value="">Please wait...</option>'
        );

        $('#export-internal-scheme-id').html(
            '<option value="">Please wait...</option>'
        );

        $('#export-internal-batch-id').html(
            '<option value="">Please wait...</option>'
        );

        $('#export-internal-institute-id').html(
            '<option value="">Please wait...</option>'
        );


        // FETCH BRANCHES
        $.get(
            "{{ url('branches') }}/" + courseId,

            function (branches) {

                $('#export-internal-branch-id').html(
                    '<option value="">--Select Branch--</option>'
                );

                $.each(branches, function (_, branch) {

                    $('#export-internal-branch-id').append(
                        `<option value="${branch.id}">${branch.name}</option>`
                    );

                });

            }

        );


        // FETCH COURSE DETAILS
        $.get(
            "{{ url('courses') }}/" + courseId + "/details",

            function (response) {


                // SCHEMES
                $('#export-internal-scheme-id').html(
                    '<option value="">--Select Scheme--</option>'
                );

                $.each(response.schemes, function (_, scheme) {

                    $('#export-internal-scheme-id').append(
                        `<option value="${scheme.id}">${scheme.name}</option>`
                    );

                });


                // BATCHES
                $('#export-internal-batch-id').html(
                    '<option value="">--Select Batch--</option>'
                );

                $.each(response.batches, function (_, batch) {

                    $('#export-internal-batch-id').append(
                        `<option value="${batch.id}">${batch.name}</option>`
                    );

                });


                // INSTITUTES
                $('#export-internal-institute-id').html(
                    '<option value="">--Select Institute--</option>'
                );

                $.each(response.institutes, function (_, institute) {

                    $('#export-internal-institute-id').append(
                        `<option value="${institute.id}">${institute.name}</option>`
                    );

                });


                let courseType = $('#export-internal-course-type').val();


                // SEMESTER BASED
                if (courseType === 'Semester Based') {

                    $('#export-internal-semester-container').show();

                    $('#export-internal-annual-semester-container').hide();

                    $('#export-internal-semester-id').html(
                        '<option value="">--Select Semester--</option>'
                    );

                    $.each(response.semesters, function (_, semester) {

                        $('#export-internal-semester-id').append(
                            `<option value="${semester.id}">${semester.name}</option>`
                        );

                    });

                }


                // ANNUAL BASED
                if (courseType === 'Annual Based') {

                    $('#export-internal-annual-semester-container').show();

                    $('#export-internal-semester-container').hide();

                    $('#export-internal-annual-semester-id').html(
                        '<option value="">--Select Annual Terms--</option>'
                    );

                    $.each(response.annual_semesters, function (_, annual) {

                        $('#export-internal-annual-semester-id').append(
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
    $('#export-internal-course-id').on('change', function () {

        loadCourseData($(this).val());

        updateMarksType();

    });


    // =========================
    // COURSE TYPE CHANGE
    // =========================
    $('#export-internal-course-type').on('change', function () {

        resetSemesterUI();

        updateMarksType();


        // RELOAD MID SEM EXAMS
        loadMidSemExams(
            $(this).val(),
            $('#export-internal-mid-sem-exam-id')
        );


        let courseId = $('#export-internal-course-id').val();

        if (courseId) {

            loadCourseData(courseId);

        }

    });


    // =========================
    // INITIAL PAGE LOAD
    // =========================
    let defaultCourse = $('#export-internal-course-id').val();

    let defaultType = $('#export-internal-course-type').val();

    updateMarksType();

    if (defaultCourse && defaultType) {

        loadCourseData(defaultCourse);

    }

});

</script>
