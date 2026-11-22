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
            url: "{{ route('external.mid-sem-marks.external.render-result') }}",
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
    // $(document).on('change', '.obtained-marks', function () {

    //     let studentId = $(this).data('student');
    //     let marks = $(this).val();
    //     let maxMarks = $(this).data('max');

    //     if (marks === '') return;

    //     saveMarks(studentId, marks, 'present', maxMarks, $(this));
    // });

    // =========================
    // AUTO SAVE OBTAINED MARKS
    // =========================
    $(document).on('change', '.obtained-marks', function () {

        let studentId = $(this).data('student');
        let marks = $(this).val();
        let maxMarks = $(this).data('max');

        if (marks === '') return;

        let row = $(this).closest('tr');

        let status = row.find('.mark-status:checked').val();

        // OB selected -> null status
        if (status === undefined || status === '') {
            status = null;
        }

        saveMarks(studentId, marks, status, maxMarks, $(this));
    });

    $(document).on('change', '.grade-radio', function () {

        let studentId = $(this).data('student');
        let grade = $(this).val();

        saveMarks(studentId, null, 'present', 0, null, grade);
    });

    // =========================
    // ABSENT CHECKBOX
    // =========================
    // $(document).on('change', '.mark-absent', function () {

    //     let studentId = $(this).data('student');

    //     let wrapper = $(this).closest('div').parent();
    //     let input = wrapper.find('.obtained-marks');
    //     let maxMarks = input.data('max');

    //     if ($(this).is(':checked')) {

    //         input.val(0);
    //         input.prop('readonly', true);

    //         saveMarks(studentId, 0, 'absent', maxMarks, input);

    //     } else {

    //         input.prop('readonly', false);

    //         let marks = input.val() === '' ? 0 : input.val();

    //         saveMarks(studentId, marks, 'present', maxMarks, input);
    //     }
    // });

    // =========================
    // RADIO STATUS (OB / AB / UMC / DT)
    // =========================
    $(document).on('change', '.mark-status', function () {

        let studentId = $(this).data('student');
        let status = $(this).val();

        let row = $(this).closest('tr');
        let input = row.find('.obtained-marks');
        let maxMarks = input.data('max');

        if (status === 'absent') {

            // AB
            input.val(0);
            input.prop('readonly', true);

            saveMarks(studentId, 0, 'absent', maxMarks, input);

        } else {

            // OB / UMC / DT -> editable
            input.prop('readonly', false);

            let marks = input.val() === '' ? 0 : input.val();

            // OB = null, UMC = umc, DT = I/F
            saveMarks(studentId, marks, status === '' ? null : status, maxMarks, input);
        }
    });

    // =========================
    // SAVE MARKS (NO TABLE RELOAD)
    // =========================
    function saveMarks(studentId, marks, examStatus, maxMarks, inputEl = null, grade = null) {

        $.ajax({
            url: "{{ route('external.mid-sem-marks.external.save') }}",
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
            url: "{{ route('external.mid-sem-marks.external.lock-unlock') }}",
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
