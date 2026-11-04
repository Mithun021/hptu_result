<script>
    // 🔴 FULL CHANGE SCRIPT
    document.getElementById('export_course_type').addEventListener('change', updateMarksType);
    document.getElementById('export_course_id').addEventListener('change', updateMarksType);

    function updateMarksType() {

        let courseType = document.getElementById('export_course_type').value;
        let courseSelect = document.getElementById('export_course_id');
        let selectedOption = courseSelect.options[courseSelect.selectedIndex];

        let courseCategory = selectedOption.getAttribute('data-course'); // UG / Diploma

        let marksType = document.getElementById('export_marks_type');

        // reset
        marksType.innerHTML = '<option value="">--Select--</option>';

        if (!courseType || !courseCategory) return;

        /* =========================
        SEMESTER BASED
        ==========================*/
        if (courseType === 'Semester Based') {

            // UG
            if (courseCategory === 'UG') {
                marksType.innerHTML += `
                    <option value="external">External</option>
                `;
            }

            // Diploma
            if (courseCategory === 'Diploma') {
                marksType.innerHTML += `
                    <option value="external">External</option>
                `;
            }
        }

        /* =========================
        ANNUAL BASED
        ==========================*/
        if (courseType === 'Annual Based') {

            // UG
            if (courseCategory === 'UG') {
                marksType.innerHTML += `
                    <option value="theory_paper1">Theory paper 1</option>
                    <option value="theory_paper2">Theory paper 2</option>
                `;
            }

            // Diploma
            if (courseCategory === 'Diploma') {
                marksType.innerHTML += `
                    <option value="theory_paper1">Theory paper 1</option>
                    <option value="theory_paper2">Theory paper 2</option>
                `;
            }
        }
    }
</script>

<script>
$(document).ready(function () {

    function resetSemesterUI() {
        $('#semester_container_export').hide();
        $('#annual_semester_container_export').hide();

        $('#export_semester_id').html('<option value="">--Select Semester--</option>');
        $('#export_annual_semester_id').html('<option value="">--Select--</option>');
    }

    function loadSubjects() {

        let courseType = $('#export_course_type').val();
        let courseId   = $('#export_course_id').val();
        let schemeId   = $('#export_scheme_id').val();
        let branchId   = $('#export_branch_id').val();
        let semesterId = $('#export_semester_id').val();
        let annualSemesterId = $('#export_annual_semester_id').val();

        if (!courseId || !branchId) {
            return;
        }

        $('#export_subject_id').html('<option>Please wait...</option>');

        $.ajax({
            url: "{{ url('result/subjects/theory-paper/filter') }}",
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

                $('#export_subject_id').html('<option value="">--Select Subject--</option>');

                if (subjects.length > 0) {
                    $.each(subjects, function (_, subject) {
                        $('#export_subject_id').append(
                            `<option value="${subject.id}">${subject.name}</option>`
                        );
                    });
                } else {
                    $('#export_subject_id').append(
                        `<option value="">No Subject Found</option>`
                    );
                }
            }
        });
    }

    $('#export_branch_id, #export_scheme_id, #export_semester_id, #export_annual_semester_id').on('change', function () {
        loadSubjects();
    });

    function loadCourseData(courseId) {
        if (!courseId) return;

        resetSemesterUI();

        /* =========================
           LOADERS
        ==========================*/
        $('#export_branch_id').html('<option>Please wait...</option>');
        $('#export_scheme_id').html('<option>Please wait...</option>');
        $('#export_batch_id').html('<option>Please wait...</option>');
        $('#export_institute_id').html('<option>Please wait...</option>');

        /* =========================
           FETCH BRANCHES
        ==========================*/
        $.get("{{ url('branches') }}/" + courseId, function (branches) {
            $('#export_branch_id').html('<option value="">--Select Branch--</option>');
            $.each(branches, function (_, branch) {
                $('#export_branch_id').append(
                    `<option value="${branch.id}">${branch.name}</option>`
                );
            });
        });

        /* =========================
           FETCH COURSE DETAILS
        ==========================*/
        $.get("{{ url('courses') }}/" + courseId + "/details", function (response) {

            /* ---------- Schemes ---------- */
            $('#export_scheme_id').html('<option value="">--Select Scheme--</option>');
            $.each(response.schemes, function (_, scheme) {
                $('#export_scheme_id').append(
                    `<option value="${scheme.id}">${scheme.name}</option>`
                );
            });

            /* ---------- Batches ---------- */
            $('#export_batch_id').html('<option value="">--Select Batch--</option>');
            $.each(response.batches, function (_, batch) {
                $('#export_batch_id').append(
                    `<option value="${batch.id}">${batch.name}</option>`
                );
            });

            /* ---------- Institutes ---------- */
            $('#export_institute_id').html('<option value="">--Select Institute--</option>');
            $.each(response.institutes, function (_, institute) {
                $('#export_institute_id').append(
                    `<option value="${institute.id}">${institute.name}</option>`
                );
            });

            let courseType = $('#export_course_type').val();

            /* =========================
               SEMESTER BASED
            ==========================*/
            if (courseType === 'Semester Based') {
                $('#semester_container_export').show();
                $('#annual_semester_container_export').hide();

                $('#export_semester_id').html('<option value="">--Select Semester--</option>');
                $.each(response.semesters, function (_, semester) {
                    $('#export_semester_id').append(
                        `<option value="${semester.id}">${semester.name}</option>`
                    );
                });
            }

            /* =========================
               ANNUAL BASED
            ==========================*/
            if (courseType === 'Annual Based') {
                $('#annual_semester_container_export').show();
                $('#semester_container_export').hide();

                $('#export_annual_semester_id').html('<option value="">--Select--</option>');
                $.each(response.annual_semesters, function (_, annual) {
                    $('#export_annual_semester_id').append(
                        `<option value="${annual.id}">${annual.name}</option>`
                    );
                });
            }
        });
    }

    /* =========================
       COURSE CHANGE
    ==========================*/
    $('#export_course_id').on('change', function () {
        loadCourseData($(this).val());
    });

    /* =========================
       COURSE TYPE CHANGE
    ==========================*/
    $('#export_course_type').on('change', function () {
        resetSemesterUI();
        let courseId = $('#export_course_id').val();
        if (courseId) {
            loadCourseData(courseId);
        }
    });

    /* =========================
       AUTO LOAD (EDIT / OLD)
    ==========================*/
    let defaultCourse = $('#export_course_id').val();
    let defaultType   = $('#export_course_type').val();

    if (defaultCourse && defaultType) {
        loadCourseData(defaultCourse);
    }

});

</script>

<script>
    $(document).ready(function () {

    // Initialize Validation
    let validator = $('#exportIndividualExternalMarksForm').validate({

        ignore: [],

        rules: {
            export_exam_id: {
                required: true
            },
            export_course_type: {
                required: true
            },
            export_course_id: {
                required: true
            },
            export_branch_id: {
                required: true
            },
            export_batch_id: {
                required: true
            },
            export_marks_type: {
                required: true
            },
            export_institute_id: {
                required: true
            },
            export_subject_id: {
                required: true
            },
            result_type: {
                required: true
            }
        },

        messages: {
            export_exam_id: {
                required: "Please select exam"
            },
            export_course_type: {
                required: "Please select course type"
            },
            export_course_id: {
                required: "Please select course"
            },
            export_branch_id: {
                required: "Please select branch"
            },
            export_batch_id: {
                required: "Please select batch"
            },
            export_marks_type: {
                required: "Please select marks type"
            },

            export_institute_id: {
                required: "Please select institute"
            },
            export_subject_id: {
                required: "Please select subject"
            },
            result_type: {
                required: "Please select result type"
            }
        },

        errorElement: 'small',
        errorClass: 'text-danger',

        highlight: function (element) {
            $(element).addClass('is-invalid');
        },

        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
        },

        errorPlacement: function (error, element) {
            error.insertAfter(element);
        }
    });

    // Export Button
    $('#applyExportFilter').on('click', function () {

        // remove old rules
        $('#export_semester_id').rules('remove');
        $('#export_annual_semester_id').rules('remove');

        let courseType = $('#export_course_type').val();

        if (courseType === 'Semester Based') {

            $('#export_semester_id').rules('add', {
                required: true,
                messages: {
                    required: 'Please select semester'
                }
            });

        } else if (courseType === 'Annual Based') {

            $('#export_annual_semester_id').rules('add', {
                required: true,
                messages: {
                    required: 'Please select annual term'
                }
            });
        }

        if (!validator.form()) {
            return false;
        }

        exportExternalMarks();
    });

});


function exportExternalMarks()
{
    let formData = $('#exportIndividualExternalMarksForm').serialize();

    $.ajax({

        url: "{{ route('retotal-result.external-marks.export') }}",

        type: "GET",

        data: formData,

        xhrFields: {
            responseType: 'blob'
        },

        beforeSend: function () {

            $('#applyExportFilter')
                .prop('disabled', true)
                .html('Exporting...');
        },

        success: function (response, status, xhr) {

            let fileName = 'external_marks_sample.csv';

            let disposition =
                xhr.getResponseHeader('Content-Disposition');

            if (disposition) {

                let match =
                    disposition.match(/filename="?([^"]+)"?/);

                if (match) {
                    fileName = match[1];
                }
            }

            let blob = new Blob([response]);

            let url =
                window.URL.createObjectURL(blob);

            let a =
                document.createElement('a');

            a.href = url;

            a.download = fileName;

            document.body.appendChild(a);

            a.click();

            a.remove();

            window.URL.revokeObjectURL(url);
        },

        error: function () {

            alert('Unable to export file.');
        },

        complete: function () {

            $('#applyExportFilter')
                .prop('disabled', false)
                .html('Export Sample');
        }
    });
}
</script>
