<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

<script>
    $(document).ready(function () {

        // =========================
        // LOAD MID SEM EXAMS
        // =========================
        function loadMidSemExams(courseType, examDropdown) {

            examDropdown.html('<option value="">--Select--</option>');

            if (courseType == '') {
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

                        examDropdown.html(
                            '<option value="">No Exam Found</option>'
                        );

                    }

                },

                error: function (xhr) {

                    console.log(xhr.responseText);

                    examDropdown.html(
                        '<option value="">Failed to load exams</option>'
                    );

                }

            });

        }


        // =========================
        // COURSE TYPE CHANGE
        // =========================
        $('#course_type, #export_course_type').on('change', function () {

            let courseType = $(this).val();

            let examDropdown = $(this).attr('id') == 'course_type'
                ? $('#mid_sem_exam_id')
                : $('#export_mid_sem_exam_id');

            loadMidSemExams(courseType, examDropdown);

        });


        // =========================
        // INITIAL LOAD - MID SEM EXAMS
        // =========================
        if ($('#course_type').length) {

            loadMidSemExams(
                $('#course_type').val(),
                $('#mid_sem_exam_id')
            );

        }

        if ($('#export_course_type').length) {

            loadMidSemExams(
                $('#export_course_type').val(),
                $('#export_mid_sem_exam_id')
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

            let courseType = $('#course_type').val();

            let courseSelect = document.getElementById('course_id');

            if (!courseSelect || courseSelect.selectedIndex < 0) {
                return;
            }

            let selectedOption = courseSelect.options[courseSelect.selectedIndex];

            let courseCategory = selectedOption.getAttribute('data-course');

            let marksType = $('#marks_type');

            marksType.html('<option value="">--Select--</option>');

            if (!courseType || !courseCategory) {
                return;
            }


            // =========================
            // SEMESTER BASED
            // =========================
            if (courseType === 'Semester Based') {

                if (courseCategory === 'UG') {

                    marksType.append(`
                        <option value="Internal">Internal</option>
                    `);

                }

                if (courseCategory === 'Diploma') {

                    marksType.append(`
                        <option value="Internal">Internal</option>
                    `);

                }

            }


            // =========================
            // ANNUAL BASED
            // =========================
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
        $('#course_type, #course_id').on('change', function () {

            updateMarksType();

        });


        // =========================
        // INITIAL LOAD - MARKS TYPE
        // =========================
        updateMarksType();

    });
</script>

<script>
$(document).ready(function () {

    // =========================
    // RESET SEMESTER UI
    // =========================
    function resetSemesterUI() {
        $('#semester_container').hide();
        $('#annual_semester_container').hide();
        $('#semester_id').html('<option value="">--Select Semester--</option>');
        $('#annual_semester_id').html('<option value="">--Select Annual Semester--</option>');
    }

    // =========================
    // LOAD SUBJECTS
    // =========================

    function loadSubjects() {

        let courseType = $('#course_type').val();
        let courseId = $('#course_id').val();
        let schemeId = $('#scheme_id').val();
        let branchId = $('#branch_id').val();
        let batchId = $('#batch_id').val();
        let semesterId = $('#semester_id').val();
        let annualSemesterId = $('#annual_semester_id').val();

        let subjectDropdown = $('#subject_id');

        // =========================
        // BASIC VALIDATION
        // =========================
        if (!courseId || !branchId) {
            subjectDropdown.html(
                '<option value="">Please select course and branch</option>'
            );
            return;
        }

        // Teacher portal requires batch
        if ($('#batch_id').length && !batchId) {
            subjectDropdown.html(
                '<option value="">Please select batch</option>'
            );
            return;
        }

        if (courseType === 'Semester Based' && !semesterId) {
            subjectDropdown.html(
                '<option value="">Please select semester</option>'
            );
            return;
        }

        if (courseType === 'Annual Based' && !annualSemesterId) {
            subjectDropdown.html(
                '<option value="">Please select annual semester</option>'
            );
            return;
        }

        subjectDropdown.html(
            '<option value="">Loading subjects...</option>'
        );

        // =========================
        // AJAX REQUEST
        // =========================
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

                subjectDropdown.empty();

                // =========================
                // SERVER RESPONSE CHECK
                // =========================
                if (!response.success) {

                    subjectDropdown.append(
                        $('<option>', {
                            value: '',
                            text: response.message || 'Unable to load subjects'
                        })
                    );

                    console.error('Subject API Error:', response);

                    return;
                }

                let subjects = response.data || [];

                subjectDropdown.append(
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

                        subjectDropdown.append(
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

                    subjectDropdown.empty().append(
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

                subjectDropdown.empty();

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

                subjectDropdown.append(
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
    $('#course_id, #branch_id, #scheme_id, #batch_id, #semester_id, #annual_semester_id')
        .on('change', function () {
            loadSubjects();
        });


    // =========================
    // LOAD COURSE DATA
    // =========================
    function loadCourseData(courseId) {

        if (!courseId) return;

        resetSemesterUI();

        $('#branch_id').html('<option>Please wait...</option>');
        $('#scheme_id').html('<option>Please wait...</option>');

        $.get("{{ url('institute/branches') }}/" + courseId, function (branches) {

            $('#branch_id').html('<option value="">--Select Branch--</option>');

            $.each(branches, function (_, branch) {
                $('#branch_id').append(
                    `<option value="${branch.id}">${branch.name}</option>`
                );
            });
        });

        $.get("{{ url('courses') }}/" + courseId + "/details", function (response) {

            $('#scheme_id').html('<option value="">--Select Scheme--</option>');

            $.each(response.schemes, function (_, scheme) {
                $('#scheme_id').append(
                    `<option value="${scheme.id}">${scheme.name}</option>`
                );
            });

            $('#batch_id').html('<option value="">--Select Batch--</option>');

            $.each(response.batches, function (_, batch) {
                $('#batch_id').append(
                    `<option value="${batch.id}">${batch.name}</option>`
                );
            });

            let courseType = $('#course_type').val();

            if (courseType === 'Semester Based') {

                $('#semester_container').show();
                $('#semester_id').html('<option value="">--Select Semester--</option>');

                $.each(response.semesters, function (_, semester) {
                    $('#semester_id').append(
                        `<option value="${semester.id}">${semester.name}</option>`
                    );
                });
            }

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

    $('#course_id').on('change', function () {
        loadCourseData($(this).val());
    });

    $('#course_type').on('change', function () {
        resetSemesterUI();

        let courseId = $('#course_id').val();

        if (courseId) {
            loadCourseData(courseId);
        }
    });

    let defaultCourse = $('#course_id').val();
    let defaultType = $('#course_type').val();

    if (defaultCourse && defaultType) {
        loadCourseData(defaultCourse);
    }

});
</script>

<script>
    // =========================
    // FILTER BUTTON
    // =========================
    $('#applyFilter').on('click', function () {

        if (!$('#mid_sem_exam_id').val()) return alert('Mid-Sem Exam Name Required');
        if (!$('#course_type').val()) return alert('Course Type Required');
        if (!$('#course_id').val()) return alert('Course Required');
        if (!$('#scheme_id').val()) return alert('Scheme Required');
        if (!$('#branch_id').val()) return alert('Branch Required');
        if (!$('#batch_id').val()) return alert('Batch Required');
        if (!$('#marks_type').val()) return alert('Marks Type Required');
        if (!$('#subject_id').val()) return alert('Subject Required');

        if ($('#course_type').val() === 'Semester Based' && !$('#semester_id').val()) {
            return alert('Semester Required');
        }

        if ($('#course_type').val() === 'Annual Based' && !$('#annual_semester_id').val()) {
            return alert('Annual Semester Required');
        }

        loadResultData();
    });

    // =========================
    // LOAD RESULT DATA
    // =========================
    function loadResultData() {

        $.ajax({
            url: "{{ route('mid-sem-marks.render-result') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                mid_sem_exam_id: $('#mid_sem_exam_id').val(),
                course_type: $('#course_type').val(),
                course_id: $('#course_id').val(),
                scheme_id: $('#scheme_id').val(),
                branch_id: $('#branch_id').val(),
                batch_id: $('#batch_id').val(),
                semester_id: $('#semester_id').val(),
                annual_semester_id: $('#annual_semester_id').val(),
                marks_type: $('#marks_type').val(),
                subject_id: $('#subject_id').val()
            },
            beforeSend: function () {
                $('#resultRenderArea').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Loading Result...</p>
                    </div>
                `);
            },
            success: function (response) {

                $('#emptyState').hide();
                $('#resultContainer').show();
                $('#resultRenderArea').html(response);

                $('#ajax_exam_title').text($('#mid_sem_exam_id option:selected').text());

                let semesterText = $('#course_type').val() === 'Semester Based'
                    ? $('#semester_id option:selected').text()
                    : $('#annual_semester_id option:selected').text();

                $('#ajax_semester_text').text(
                    semesterText + ' - Session: ' + $('#batch_id option:selected').text()
                );

                $('#ajax_subject_text').text($('#subject_id option:selected').text());
            }
        });
    }
</script>

<script>
    // =========================
    // MARKS VALIDATION
    // =========================
    $(document).on('input', '.obtained-marks', function () {

        let maxMarks = parseFloat($(this).data('max'));
        let value = parseFloat($(this).val());

        if (value > maxMarks) {
            alert('Marks cannot be greater than Maximum Marks (' + maxMarks + ')');
            $(this).val('');
        }
    });

    // =========================
    // AUTO SAVE MARKS (NO RELOAD)
    // =========================
    $(document).on('change', '.obtained-marks', function () {

        let studentId = $(this).data('student');
        let marks = $(this).val();
        let maxMarks = $(this).data('max');

        if (marks === '') return;

        saveMarks(studentId, marks, 'present', maxMarks, $(this));
    });

    $(document).on('change', '.grade-radio', function () {

        let studentId = $(this).data('student');
        let grade = $(this).val();

        saveMarks(studentId, null, 'present', 0, null, grade);
    });

    // =========================
    // ABSENT CHECKBOX
    // =========================
    $(document).on('change', '.mark-absent', function () {

        let studentId = $(this).data('student');

        let wrapper = $(this).closest('div').parent();
        let input = wrapper.find('.obtained-marks');
        let maxMarks = input.data('max');

        if ($(this).is(':checked')) {

            input.val(0);
            input.prop('readonly', true);

            saveMarks(studentId, 0, 'absent', maxMarks, input);

        } else {

            input.prop('readonly', false);

            let marks = input.val() === '' ? 0 : input.val();

            saveMarks(studentId, marks, 'present', maxMarks, input);
        }
    });

    // =========================
    // SAVE MARKS (NO TABLE RELOAD)
    // =========================
    function saveMarks(studentId, marks, examStatus, maxMarks, inputEl = null, grade = null) {

        $.ajax({
            url: "{{ route('mid-sem-marks.save') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                student_id: studentId,
                marks: marks,
                exam_status: examStatus,
                max_marks: maxMarks,
                grade: grade,
                mid_sem_exam_id: $('#mid_sem_exam_id').val(),
                subject_id: $('#subject_id').val(),
                course_type: $('#course_type').val(),
                course_id: $('#course_id').val(),
                branch_id: $('#branch_id').val(),
                batch_id: $('#batch_id').val(),
                scheme_id: $('#scheme_id').val(),
                semester_id: $('#semester_id').val(),
                annual_semester_id: $('#annual_semester_id').val(),
                marks_type: $('#marks_type').val(),
            },
            // success: function () {
            //     if (inputEl) {
            //         inputEl.addClass('border border-success');

            //         setTimeout(() => {
            //             inputEl.removeClass('border border-success');
            //         }, 800);
            //     }

            //     Toastify({
            //         text: "Marks saved successfully",
            //         duration: 1500,
            //         gravity: "top",
            //         position: "right",
            //         close: true,
            //         backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
            //     }).showToast();
            // },
            success: function (response) {

                if (inputEl) {
                    inputEl.addClass('border border-success');

                    setTimeout(() => {
                        inputEl.removeClass('border border-success');
                    }, 800);
                }

                // SHOW CHECKBOX AFTER SAVE
                if (response.id) {

                    let checkbox = $('input.student-checkbox[data-id="' + response.id + '"]');

                    if (checkbox.length) {
                        checkbox
                            .attr('data-id', response.id)
                            .prop('disabled', false)
                            .show();
                    } else {
                        let row = $('.obtained-marks[data-student="' + studentId + '"]').closest('tr');

                        row.find('td:first').html(`
                            <input type="checkbox"
                                class="student-checkbox"
                                data-id="${response.id}">
                        `);
                    }
                }

                Toastify({
                    text: "Marks saved successfully",
                    duration: 1500,
                    gravity: "top",
                    position: "right",
                    close: true,
                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                }).showToast();
            },
            error: function () {
                Toastify({
                    text: "Something went wrong!",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    close: true,
                    backgroundColor: "linear-gradient(to right, #ff416c, #ff4b2b)"
                }).showToast();
            }
        });
    }
</script>

<script>
    // =========================
    // LOCK / UNLOCK / FORWARD
    // =========================
    function sendLockUnlockRequest(ids, action) {

        $('#apply_lock_action').prop('disabled', true);

        $.ajax({
            url: "{{ route('mid-sem-marks.lock-unlock') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                ids: ids,
                action: action
            },
            success: function (res) {

                $('#apply_lock_action').prop('disabled', false);

                if (!res.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Action Failed',
                        text: res.message
                    });
                    return;
                }

                Toastify({
                    text: res.message,
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                }).showToast();

                // ✅ Reload only here (important)
                loadResultData();

                $('#select_all_students').prop('checked', false);
            },
            error: function () {

                $('#apply_lock_action').prop('disabled', false);

                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Something went wrong. Please try again.'
                });
            }
        });
    }

    $(document).on('change', '#select_all_students', function () {
        $('.student-checkbox').prop('checked', $(this).is(':checked'));
    });

    $('#apply_lock_action').on('click', function () {

        let action = $('#lock_action').val();

        if (!action) {
            Swal.fire({
                icon: 'warning',
                title: 'Select Action',
                text: 'Please select an action first.'
            });
            return;
        }

        let ids = [];

        $('.student-checkbox:checked').each(function () {
            ids.push($(this).data('id'));
        });

        if (ids.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one student.'
            });
            return;
        }

        if (action === 'forward') {

            Swal.fire({
                title: 'Are you sure?',
                text: "After you forward the marks you won't be able to edit them.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Forward It!',
                cancelButtonText: 'Cancel'
            }).then((result) => {

                if (result.isConfirmed) {
                    sendLockUnlockRequest(ids, action);
                }
            });

            return;
        }

        sendLockUnlockRequest(ids, action);
    });
</script>
