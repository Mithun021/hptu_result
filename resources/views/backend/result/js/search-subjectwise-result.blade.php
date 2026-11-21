
<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

<script>
$(document).ready(function () {

    // =====================================================
    // 1. SUBJECT-WISE OFFCANVAS
    // Original HTML IDs - DO NOT CHANGE
    // =====================================================


    // =========================
    // LOAD MID-SEM EXAMS BY EXAM ID
    // =========================
    $('#exam_id').on('change', function () {

        let examId = $(this).val();

        let midSemDropdown = $('#midsem_exam_id');

        midSemDropdown.html('<option value="">--Select--</option>');

        if (!examId) {
            return;
        }

        $.ajax({

            url: "{{ url('/get-midsem-exams') }}/" + examId,

            type: "GET",

            dataType: "json",

            beforeSend: function () {
                midSemDropdown.html(
                    '<option value="">Please wait...</option>'
                );
            },

            success: function (data) {

                midSemDropdown.html(
                    '<option value="">--Select--</option>'
                );

                if (data && data.length > 0) {

                    $.each(data, function (key, value) {

                        midSemDropdown.append(
                            $('<option>', {
                                value: value.id,
                                text: value.mid_sem_exam_name
                            })
                        );

                    });

                } else {

                    midSemDropdown.html(
                        '<option value="">No Exam Found</option>'
                    );

                }

            },

            error: function (xhr) {

                console.log('Subject-wise Mid-Sem Error:', xhr.responseText);

                midSemDropdown.html(
                    '<option value="">Failed to load exams</option>'
                );

            }

        });

    });


    // =========================
    // SUBJECT-WISE MARKS TYPE
    // =========================
    function updateSubjectwiseMarksType() {

        let courseType = $('#course_type').val();

        let selectedOption = $('#course_id option:selected');

        let courseCategory = selectedOption.data('course');

        let marksType = $('#marks_type');

        marksType.html('<option value="">--Select--</option>');

        if (!courseType || !courseCategory) {
            return;
        }


        // SEMESTER BASED
        if (courseType === 'Semester Based') {

            if (courseCategory === 'UG' || courseCategory === 'Diploma') {

                marksType.append(
                    '<option value="Internal">Internal</option>'
                );

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
    // RESET SUBJECT-WISE DROPDOWNS
    // =========================
    function resetSubjectwiseDropdowns() {

        $('#scheme_id').html(
            '<option value="">--Select Scheme--</option>'
        );

        $('#branch_id').html(
            '<option value="">--Select Branch--</option>'
        );

        $('#batch_id').html(
            '<option value="">--Select Batch--</option>'
        );

        $('#institute_id').html(
            '<option value="">--Select Institute--</option>'
        );

        $('#semester_id').html(
            '<option value="">--Select Semester--</option>'
        );

        $('#annual_semester_id').html(
            '<option value="">--Select Annual Terms--</option>'
        );

        $('#subject_id').html(
            '<option value="">--Select Subject--</option>'
        );

        $('#semester_container').hide();

        $('#annual_semester_container').hide();

    }


    // =========================
    // LOAD SUBJECT-WISE SUBJECTS
    // =========================
    function loadSubjectwiseSubjects() {

        let courseType = $('#course_type').val();

        let courseId = $('#course_id').val();

        let schemeId = $('#scheme_id').val();

        let branchId = $('#branch_id').val();

        let semesterId = $('#semester_id').val();

        let annualSemesterId = $('#annual_semester_id').val();


        if (!courseId || !branchId) {

            $('#subject_id').html(
                '<option value="">--Select Subject--</option>'
            );

            return;
        }


        $('#subject_id').html(
            '<option value="">Please wait...</option>'
        );


        $.ajax({

            url: "{{ url('result/subjects/filter') }}",

            type: "GET",

            dataType: "json",

            data: {

                course_type: courseType,

                course_id: courseId,

                scheme_id: schemeId,

                branch_id: branchId,

                semester_id: semesterId,

                annual_semester_id: annualSemesterId

            },

            success: function (subjects) {

                $('#subject_id').html(
                    '<option value="">--Select Subject--</option>'
                );

                if (subjects && subjects.length > 0) {

                    $.each(subjects, function (_, subject) {

                        $('#subject_id').append(
                            $('<option>', {
                                value: subject.id,
                                text: subject.name
                            })
                        );

                    });

                } else {

                    $('#subject_id').append(
                        '<option value="">No Subject Found</option>'
                    );

                }

            },

            error: function (xhr) {

                console.log('Subject-wise Subjects Error:', xhr.responseText);

                $('#subject_id').html(
                    '<option value="">Failed to load subjects</option>'
                );

            }

        });

    }


    // =========================
    // LOAD SUBJECT-WISE COURSE DATA
    // =========================
    function loadSubjectwiseCourseData(courseId) {

        resetSubjectwiseDropdowns();

        if (!courseId) {
            return;
        }


        // LOADERS
        $('#branch_id').html(
            '<option value="">Please wait...</option>'
        );

        $('#scheme_id').html(
            '<option value="">Please wait...</option>'
        );

        $('#batch_id').html(
            '<option value="">Please wait...</option>'
        );

        $('#institute_id').html(
            '<option value="">Please wait...</option>'
        );


        // =========================
        // LOAD BRANCHES
        // =========================
        $.ajax({

            url: "{{ url('branches') }}/" + courseId,

            type: "GET",

            dataType: "json",

            success: function (branches) {

                $('#branch_id').html(
                    '<option value="">--Select Branch--</option>'
                );

                $.each(branches || [], function (_, branch) {

                    $('#branch_id').append(
                        $('<option>', {
                            value: branch.id,
                            text: branch.name
                        })
                    );

                });

            },

            error: function (xhr) {

                console.log('Subject-wise Branch Error:', xhr.responseText);

                $('#branch_id').html(
                    '<option value="">Failed to load branches</option>'
                );

            }

        });


        // =========================
        // LOAD COURSE DETAILS
        // =========================
        $.ajax({

            url: "{{ url('courses') }}/" + courseId + "/details",

            type: "GET",

            dataType: "json",

            success: function (response) {


                // SCHEMES
                $('#scheme_id').html(
                    '<option value="">--Select Scheme--</option>'
                );

                $.each(response.schemes || [], function (_, scheme) {

                    $('#scheme_id').append(
                        $('<option>', {
                            value: scheme.id,
                            text: scheme.name
                        })
                    );

                });


                // BATCHES
                $('#batch_id').html(
                    '<option value="">--Select Batch--</option>'
                );

                $.each(response.batches || [], function (_, batch) {

                    $('#batch_id').append(
                        $('<option>', {
                            value: batch.id,
                            text: batch.name
                        })
                    );

                });


                // INSTITUTES
                $('#institute_id').html(
                    '<option value="">--Select Institute--</option>'
                );

                $.each(response.institutes || [], function (_, institute) {

                    $('#institute_id').append(
                        $('<option>', {
                            value: institute.id,
                            text: institute.name
                        })
                    );

                });


                let courseType = $('#course_type').val();


                // SEMESTER BASED
                if (courseType === 'Semester Based') {

                    $('#semester_container').show();

                    $('#annual_semester_container').hide();

                    $('#semester_id').html(
                        '<option value="">--Select Semester--</option>'
                    );

                    $.each(response.semesters || [], function (_, semester) {

                        $('#semester_id').append(
                            $('<option>', {
                                value: semester.id,
                                text: semester.name
                            })
                        );

                    });

                }


                // ANNUAL BASED
                if (courseType === 'Annual Based') {

                    $('#annual_semester_container').show();

                    $('#semester_container').hide();

                    $('#annual_semester_id').html(
                        '<option value="">--Select Annual Terms--</option>'
                    );

                    $.each(response.annual_semesters || [], function (_, annual) {

                        $('#annual_semester_id').append(
                            $('<option>', {
                                value: annual.id,
                                text: annual.name
                            })
                        );

                    });

                }

            },

            error: function (xhr) {

                console.log('Subject-wise Course Details Error:', xhr.responseText);

                $('#scheme_id, #batch_id, #institute_id').html(
                    '<option value="">Failed to load data</option>'
                );

            }

        });

    }


    // =========================
    // SUBJECT-WISE COURSE CHANGE
    // =========================
    $('#course_id').on('change', function () {

        let courseId = $(this).val();

        updateSubjectwiseMarksType();

        loadSubjectwiseCourseData(courseId);

    });


    // =========================
    // SUBJECT-WISE COURSE TYPE CHANGE
    // =========================
    $('#course_type').on('change', function () {

        updateSubjectwiseMarksType();

        let courseId = $('#course_id').val();

        if (courseId) {

            loadSubjectwiseCourseData(courseId);

        } else {

            resetSubjectwiseDropdowns();

        }

    });


    // =========================
    // SUBJECT-WISE SUBJECT FILTER EVENTS
    // =========================
    $(
        '#scheme_id, #branch_id, #semester_id, #annual_semester_id'
    ).on('change', function () {

        loadSubjectwiseSubjects();

    });


    // =========================
    // INITIAL SUBJECT-WISE LOAD
    // =========================
    updateSubjectwiseMarksType();


    // =====================================================
    // 2. COMMON FUNCTION
    // =====================================================

    // PREVENT ARROW UP/DOWN IN MARKS INPUT
    $(document).on('keydown', '.obtained-marks', function (e) {

        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {

            e.preventDefault();

        }

    });


    // =====================================================
    // 3. EXPORT INTERNAL OFFCANVAS
    // Uses export-internal-* IDs only
    // =====================================================


    // =========================
    // LOAD EXPORT MID-SEM EXAMS
    // =========================
    function loadExportMidSemExams(courseType) {

        let examDropdown = $('#export-internal-mid-sem-exam-id');

        examDropdown.html(
            '<option value="">--Select--</option>'
        );

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

                examDropdown.html(
                    '<option value="">Loading...</option>'
                );

            },

            success: function (response) {

                examDropdown.html(
                    '<option value="">--Select--</option>'
                );

                if (response.status && response.data && response.data.length > 0) {

                    $.each(response.data, function (_, exam) {

                        examDropdown.append(
                            $('<option>', {
                                value: exam.id,
                                text: exam.mid_sem_exam_name
                            })
                        );

                    });

                } else {

                    examDropdown.html(
                        '<option value="">No Exam Found</option>'
                    );

                }

            },

            error: function (xhr) {

                console.log('Export Mid-Sem Error:', xhr.responseText);

                examDropdown.html(
                    '<option value="">Failed to load exams</option>'
                );

            }

        });

    }


    // =========================
    // EXPORT MARKS TYPE
    // =========================
    function updateExportMarksType() {

        let courseType = $('#export-internal-course-type').val();

        let selectedOption = $('#export-internal-course-id option:selected');

        let courseCategory = selectedOption.data('course');

        let marksType = $('#export-internal-marks-type');

        marksType.html(
            '<option value="">--Select--</option>'
        );

        if (!courseType || !courseCategory) {
            return;
        }


        // SEMESTER BASED
        if (courseType === 'Semester Based') {

            if (courseCategory === 'UG' || courseCategory === 'Diploma') {

                marksType.append(
                    '<option value="Internal">Internal</option>'
                );

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
    // RESET EXPORT SEMESTER UI
    // =========================
    function resetExportSemesterUI() {

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
    // LOAD EXPORT SUBJECTS
    // =========================
    function loadExportSubjects() {

        let courseType = $('#export-internal-course-type').val();

        let courseId = $('#export-internal-course-id').val();

        let schemeId = $('#export-internal-scheme-id').val();

        let branchId = $('#export-internal-branch-id').val();

        let semesterId = $('#export-internal-semester-id').val();

        let annualSemesterId = $('#export-internal-annual-semester-id').val();


        if (!courseId || !branchId) {

            $('#export-internal-subject-id').html(
                '<option value="">--Select Subject--</option>'
            );

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

                if (subjects && subjects.length > 0) {

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

                console.log('Export Subjects Error:', xhr.responseText);

                $('#export-internal-subject-id').html(
                    '<option value="">Failed to load subjects</option>'
                );

            }

        });

    }


    // =========================
    // EXPORT SUBJECT FILTER EVENTS
    // =========================
    $(
        '#export-internal-branch-id, ' +
        '#export-internal-scheme-id, ' +
        '#export-internal-semester-id, ' +
        '#export-internal-annual-semester-id'
    ).on('change', function () {

        loadExportSubjects();

    });


    // =========================
    // LOAD EXPORT COURSE DATA
    // =========================
    function loadExportCourseData(courseId) {

        resetExportSemesterUI();

        if (!courseId) {

            $('#export-internal-branch-id, #export-internal-scheme-id, #export-internal-batch-id, #export-internal-institute-id').html(
                '<option value="">--Select--</option>'
            );

            return;
        }


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


        // =========================
        // LOAD BRANCHES
        // =========================
        $.ajax({

            url: "{{ url('branches') }}/" + courseId,

            type: "GET",

            dataType: "json",

            success: function (branches) {

                $('#export-internal-branch-id').html(
                    '<option value="">--Select Branch--</option>'
                );

                $.each(branches || [], function (_, branch) {

                    $('#export-internal-branch-id').append(
                        $('<option>', {
                            value: branch.id,
                            text: branch.name
                        })
                    );

                });

            },

            error: function (xhr) {

                console.log('Export Branch Error:', xhr.responseText);

                $('#export-internal-branch-id').html(
                    '<option value="">Failed to load branches</option>'
                );

            }

        });


        // =========================
        // LOAD COURSE DETAILS
        // =========================
        $.ajax({

            url: "{{ url('courses') }}/" + courseId + "/details",

            type: "GET",

            dataType: "json",

            success: function (response) {


                // SCHEMES
                $('#export-internal-scheme-id').html(
                    '<option value="">--Select Scheme--</option>'
                );

                $.each(response.schemes || [], function (_, scheme) {

                    $('#export-internal-scheme-id').append(
                        $('<option>', {
                            value: scheme.id,
                            text: scheme.name
                        })
                    );

                });


                // BATCHES
                $('#export-internal-batch-id').html(
                    '<option value="">--Select Batch--</option>'
                );

                $.each(response.batches || [], function (_, batch) {

                    $('#export-internal-batch-id').append(
                        $('<option>', {
                            value: batch.id,
                            text: batch.name
                        })
                    );

                });


                // INSTITUTES
                $('#export-internal-institute-id').html(
                    '<option value="">--Select Institute--</option>'
                );

                $.each(response.institutes || [], function (_, institute) {

                    $('#export-internal-institute-id').append(
                        $('<option>', {
                            value: institute.id,
                            text: institute.name
                        })
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

                    $.each(response.semesters || [], function (_, semester) {

                        $('#export-internal-semester-id').append(
                            $('<option>', {
                                value: semester.id,
                                text: semester.name
                            })
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

                    $.each(response.annual_semesters || [], function (_, annual) {

                        $('#export-internal-annual-semester-id').append(
                            $('<option>', {
                                value: annual.id,
                                text: annual.name
                            })
                        );

                    });

                }

            },

            error: function (xhr) {

                console.log('Export Course Details Error:', xhr.responseText);

                $('#export-internal-scheme-id, #export-internal-batch-id, #export-internal-institute-id').html(
                    '<option value="">Failed to load data</option>'
                );

            }

        });

    }


    // =========================
    // EXPORT COURSE CHANGE
    // =========================
    $('#export-internal-course-id').on('change', function () {

        updateExportMarksType();

        loadExportCourseData($(this).val());

    });


    // =========================
    // EXPORT COURSE TYPE CHANGE
    // =========================
    $('#export-internal-course-type').on('change', function () {

        updateExportMarksType();

        loadExportMidSemExams($(this).val());

        let courseId = $('#export-internal-course-id').val();

        if (courseId) {

            loadExportCourseData(courseId);

        } else {

            resetExportSemesterUI();

        }

    });


    // =========================
    // INITIAL EXPORT LOAD
    // =========================
    if ($('#export-internal-course-type').length) {

        loadExportMidSemExams(
            $('#export-internal-course-type').val()
        );

        updateExportMarksType();

        let defaultExportCourse = $('#export-internal-course-id').val();

        if (defaultExportCourse) {

            loadExportCourseData(defaultExportCourse);

        }

    }

});
</script>
