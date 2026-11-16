<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

<script>

    $(document).ready(function () {

        $('#course_type, #export_course_type').on('change', function () {

            let courseType = $(this).val();

            // Identify corresponding dropdown
            let examDropdown = $(this).attr('id') == 'course_type'
                ? $('#mid_sem_exam_id')
                : $('#export_mid_sem_exam_id');

            // Reset dropdown
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

        });

    });

    $(document).on('keydown', '.obtained-marks', function(e) {
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            e.preventDefault();
        }
    });

    // =========================
    // MARKS TYPE CHANGE
    // =========================
    document.getElementById('course_type').addEventListener('change', updateMarksType);
    document.getElementById('course_id').addEventListener('change', updateMarksType);

    function updateMarksType() {

        let courseType = document.getElementById('course_type').value;
        let courseSelect = document.getElementById('course_id');
        let selectedOption = courseSelect.options[courseSelect.selectedIndex];
        let courseCategory = selectedOption.getAttribute('data-course');

        let marksType = document.getElementById('marks_type');

        marksType.innerHTML = '<option value="">--Select--</option>';

        if (!courseType || !courseCategory) return;

        // Semester Based
        if (courseType === 'Semester Based') {

            if (courseCategory === 'UG') {
                marksType.innerHTML += `
                    <option value="Mid-Sem">Mid-Sem</option>
                    <option value="Quiz">Quiz</option>
                    <option value="Practical">Practical</option>
                `;
            }

            if (courseCategory === 'Diploma') {
                marksType.innerHTML += `
                    <option value="Mid-Sem">Mid-Sem</option>
                `;
            }
        }

        // Annual Based
        if (courseType === 'Annual Based') {

            if (courseCategory === 'UG') {
                marksType.innerHTML += `
                    <option value="Internal 1">Internal 1</option>
                    <option value="Internal 2">Internal 2</option>
                    <option value="Internal 3">Internal 3</option>
                    
                `;
            }

            if (courseCategory === 'Diploma') {
                marksType.innerHTML += `
                    <option value="Internal 1">Internal 1</option>
                    <option value="Internal 2">Internal 2</option>
                    <option value="Internal 3">Internal 3</option>
                `;
            }
        }
    }
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
        let semesterId = $('#semester_id').val();
        let annualSemesterId = $('#annual_semester_id').val();

        if (!courseId || !branchId) {
            return;
        }
 
        $('#subject_id').html('<option>Please wait...</option>');

        $.ajax({
            url: "{{ url('institute/subjects/filter') }}",
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
                    $('#subject_id').append('<option value="">No Subject Found</option>');
                }
            }
        });
    }

    $('#branch_id, #scheme_id, #semester_id, #annual_semester_id').on('change', function () {
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