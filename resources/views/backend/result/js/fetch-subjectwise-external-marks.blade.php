<script src="{{ asset('assets/toastify/toastify-js') }}"></script>
<script>

$(document).ready(function () {

    /*
    ==========================================
    FORM VALIDATION
    ==========================================
    */

    let validator = $("#subjectwiseOffcanvasTop form#searchIndividualExternalMarksForm").validate({

        rules: {

            exam_id: { required: true },

            course_type: { required: true },

            course_id: { required: true },

            branch_id: { required: true },

            batch_id: { required: true },

            marks_type: { required: true },

            subject_id: { required: true },

            semester_id: {
                required: function () {
                    return $("#course_type").val() === "Semester Based";
                }
            },

            annual_semester_id: {
                required: function () {
                    return $("#course_type").val() === "Annual Based";
                }
            }

        },

        messages: {

            exam_id: "Please select exam",

            course_type: "Select course type",

            course_id: "Select course",

            branch_id: "Select branch",

            batch_id: "Select batch",

            semester_id: "Select semester",

            annual_semester_id: "Select annual semester",

            marks_type: "Select marks type",

            subject_id: "Select subject",

        }

    });



    /*
    ==========================================
    FUNCTION : LOAD INTERNAL MARKS
    ==========================================
    */

    function loadExternalMarks()
    {

        let formData = $("#searchIndividualExternalMarksForm").serialize();

        // console.log(formData); return false;

        $.ajax({

            url: "{{ route('results.subject-wise.external-marks.fetch') }}",

            type: "GET",

            data: formData,

            beforeSend: function ()
            {

                $("#subjectwiseExternalMarksResult").html(

                    '<div class="text-center p-5">' +

                    '<span class="spinner-border spinner-border-sm"></span> ' +

                    'Loading External Marks...' +

                    '</div>'

                );

            },

            success: function (response)
            {

                $("#subjectwiseExternalMarksResult").html(response);

            },

            error: function ()
            {

                $("#subjectwiseExternalMarksResult").html(

                    '<div class="alert alert-danger text-center">' +

                    'Failed to load external marks. Please try again.' +

                    '</div>'

                );

            }

        });

    }



    /*
    ==========================================
    FILTER BUTTON CLICK
    ==========================================
    */

    $("#applyFilter").on("click", function () {

        if (!validator.form()) {
            return;
        }

        loadExternalMarks();

    });


    /*
    =====================================================
    TOAST
    =====================================================
    */

    function showToast(message, type = "success") {

        let bgColor = "#28a745";

        if(type === "error"){
            bgColor = "#dc3545";
        }

        if(type === "warning"){
            bgColor = "#ffc107";
        }

        Toastify({
            text: message,
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: bgColor,
            stopOnFocus: true
        }).showToast();
    }


    /*
    =====================================================
    SAVE OBTAINED MARKS
    =====================================================
    */

    $(document).on("change", ".save-marks", function () {

        const input = $(this);

        const studentId = input.data("student");
        const subjectId = input.data("subject");
        const marks = input.val();
        const cell = input.closest("td");

        if (marks === '') return;

        const selectedStatus = cell.find(".mark-status:checked").val();

        // OB => null status
        const status = selectedStatus === undefined || selectedStatus === ''
            ? null
            : selectedStatus;

        saveExternalMarks(studentId, subjectId, marks, status, input);
    });


    /*
    =====================================================
    RADIO STATUS (OB / AB / UMC / I/F)
    =====================================================
    */

    $(document).on("change", ".mark-status", function () {

        const radio = $(this);

        const studentId = radio.data("student");
        const subjectId = radio.data("subject");
        const status = radio.val();

        const cell = radio.closest("td");
        const input = cell.find(".save-marks");

        const maxMarks = input.data("max");

        if (status === "absent") {

            // AB
            input.val(0);
            input.prop("readonly", true);

            saveExternalMarks(
                studentId,
                subjectId,
                0,
                "absent",
                input
            );

        } else {

            // OB / UMC / I/F -> Editable
            input.prop("readonly", false);

            let marks = input.val() === ''
                ? 0
                : input.val();

            // OB value empty => NULL
            let saveStatus = status === ''
                ? null
                : status;

            saveExternalMarks(
                studentId,
                subjectId,
                marks,
                saveStatus,
                input
            );
        }

    });


    /*
    =====================================================
    SAVE FUNCTION
    =====================================================
    */

    function saveExternalMarks(studentId, subjectId, marks, status, input)
    {

        const maxMarks = parseFloat(input.data("max"));

        const marksType = input.attr("data-paper") || "external";

        if (marks !== "" && parseFloat(marks) > maxMarks)
        {

            showToast(
                "Marks cannot be greater than Max Marks (" + maxMarks + ")",
                "error"
            );

            input.val("");

            input.css("background", "#f8d7da");

            return;
        }

        const formData = $("#searchIndividualExternalMarksForm")
            .serializeArray();

        const payload = {};

        formData.forEach(function (item) {

            payload[item.name] = item.value;

        });

        payload.student_id = studentId;
        payload.subject_id = subjectId;
        payload.marks      = marks;
        payload.status     = status;
        payload.max_marks  = input.data("max");
        payload.marks_type = marksType;

        $.ajax({

            url: "{{ route('results.external-marks.save') }}",

            type: "POST",

            data: payload,

            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },

            beforeSend: function () {

                input.css("background", "#fff3cd");

            },

            success: function () {

                input.css("background", "#d4edda");

                showToast(
                    "Marks saved successfully",
                    "success"
                );

                setTimeout(function () {

                    input.css("background", "");

                }, 800);

            },

            error: function () {

                input.css("background", "#f8d7da");

                showToast(
                    "Failed to save marks",
                    "error"
                );

            }

        });

    }


    /*
    =====================================================
    CHECK ALL
    =====================================================
    */

    $(document).on("change", "#check-all", function () {

        $(".lock-check").prop(
            "checked",
            $(this).is(":checked")
        );

    });


    /*
    =====================================================
    LOCK / UNLOCK
    =====================================================
    */

    $("#mark_status").on("change", function ()
    {

        const status = $(this).val();

        if(status === "") return;

        let selected = [];

        $(".lock-check:checked").each(function () {

            selected.push({

                student_id: $(this).data("student"),
                subject_id: $(this).data("subject")

            });

        });

        if(selected.length === 0)
        {

            showToast(
                "Please select at least one checkbox",
                "warning"
            );

            $(this).val("");

            return;
        }

        const formData = $("#searchIndividualExternalMarksForm")
            .serializeArray();

        const payload = {};

        formData.forEach(function (item) {

            payload[item.name] = item.value;

        });

        payload.records = selected;
        payload.status  = status;

        $.ajax({

            url: "{{ route('results.external-marks.update-lock') }}",

            type: "POST",

            data: payload,

            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },

            beforeSend: function () {

                $("#mark_status").prop("disabled", true);

            },

            success: function () {

                $("#mark_status").prop("disabled", false);

                loadExternalMarks();

            },

            error: function () {

                $("#mark_status").prop("disabled", false);

                showToast(
                    "Update failed",
                    "error"
                );

            }

        });

    });



});

$(document).ready(function () {

    /*
    ==========================================
    TOAST FUNCTION
    ==========================================
    */

    function showToast(message, type = "success")
    {

        let bgColor = "#28a745";

        if(type === "error"){
            bgColor = "#dc3545";
        }

        if(type === "warning"){
            bgColor = "#ffc107";
        }

        Toastify({

            text: message,

            duration: 3000,

            gravity: "top",

            position: "right",

            backgroundColor: bgColor,

            stopOnFocus: true

        }).showToast();
    }


    /*
    ==========================================
    SAVE ACADEMIC ADJUSTMENT
    ==========================================
    */

    $(document).on('click', '.save-adjustment', function () {

        let button = $(this);

        let studentId = button.data('student');
        let subjectId = button.data('subject');
        let examId    = button.data('exam');

        let adjustmentScope = button.data('adjustment-scope');

        let marksType = button.data('marks-type');

        let row = button.closest('td');

        let adjustmentMarks = row.find('.grace-marks-input').val();
        let adjustmentType  = row.find('.adjustment-type').val();

        /*
        ==========================================
        VALIDATION
        ==========================================
        */

        if(adjustmentType == ''){

            showToast(
                'Please select adjustment type',
                'error'
            );

            return;
        }

        /*
        ==========================================
        AJAX
        ==========================================
        */

        $.ajax({

            url: "{{ route('results.save.academic.adjustment') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                exam_id: examId,

                student_id: studentId,
                subject_id: subjectId,

                institute_id: $('#institute_id').val(),

                course_type: $('#course_type').val(),

                course_id: $('#course_id').val(),
                branch_id: $('#branch_id').val(),
                batch_id: $('#batch_id').val(),

                semester_id: $('#semester_id').val(),
                annual_semester_id: $('#annual_semester_id').val(),
                scheme_id: $('#scheme_id').val(),

                adjustment_marks: adjustmentMarks,
                adjustment_type: adjustmentType,
                adjustment_scope: adjustmentScope,

                marks_type: marksType,
            },

            /*
            ==========================================
            BEFORE SEND
            ==========================================
            */

            beforeSend: function () {

                button.prop('disabled', true);

                button.removeClass('btn-primary')
                    .addClass('btn-secondary');

                button.html('Please Wait...');
            },

            /*
            ==========================================
            SUCCESS
            ==========================================
            */

            success: function (response) {

                showToast(
                    response.message,
                    'success'
                );

                button.prop('disabled', false);

                button.removeClass('btn-secondary')
                    .addClass('btn-primary');

                button.html('Save');
            },

            /*
            ==========================================
            ERROR
            ==========================================
            */

            error: function (xhr) {

                let message = 'Something went wrong';

                if(xhr.responseJSON &&
                    xhr.responseJSON.message)
                {
                    message = xhr.responseJSON.message;
                }

                showToast(message, 'error');

                button.prop('disabled', false);

                button.removeClass('btn-secondary')
                    .addClass('btn-primary');

                button.html('Save');
            }
        });

    });


    /*
    ==========================================
    SAVE GRACE MARKS
    ==========================================
    */

    $(document).on('click', '.save-grace-marks', function () {

        let button = $(this);

        let studentId = button.data('student');
        let subjectId = button.data('subject');
        let examId    = button.data('exam');

        let marksType = button.data('marks-type');

        let row = button.closest('td');

        let graceMarks = row.find('.grace-mark-input').val();

        /*
        ==========================================
        AJAX
        ==========================================
        */

        $.ajax({

            url: "{{ route('results.external-marks.save-grace-marks') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                exam_id: examId,

                student_id: studentId,
                subject_id: subjectId,

                institute_id: $('#institute_id').val(),

                course_type: $('#course_type').val(),

                course_id: $('#course_id').val(),
                branch_id: $('#branch_id').val(),
                batch_id: $('#batch_id').val(),

                semester_id: $('#semester_id').val(),
                annual_semester_id: $('#annual_semester_id').val(),
                scheme_id: $('#scheme_id').val(),

                marks_type: marksType,

                grace_marks: graceMarks,

            },

            /*
            ==========================================
            BEFORE SEND
            ==========================================
            */

            beforeSend: function () {

                button.prop('disabled', true);

                button.removeClass('btn-success')
                    .addClass('btn-secondary');

                button.html('Please Wait...');
            },

            /*
            ==========================================
            SUCCESS
            ==========================================
            */

            success: function (response) {

                showToast(
                    response.message,
                    'success'
                );

                button.prop('disabled', false);

                button.removeClass('btn-secondary')
                    .addClass('btn-success');

                button.html('Save');
            },

            /*
            ==========================================
            ERROR
            ==========================================
            */

            error: function (xhr) {

                let message = 'Something went wrong';

                if(xhr.responseJSON &&
                    xhr.responseJSON.message)
                {
                    message = xhr.responseJSON.message;
                }

                showToast(message, 'error');

                button.prop('disabled', false);

                button.removeClass('btn-secondary')
                    .addClass('btn-success');

                button.html('Save');
            }

        });

    });


    // ----------------------------------------------------------------
        // Annual Moderation marks ---------------------------------
    // ----------------------------------------------------------------

    $(document).on('click', '#apply-annual-moderation-btn', function () {

        let button = $(this);

        /*
        ==========================================
        FORM DATA
        ==========================================
        */

        let examId            = $('input[name="exam_id"]').val();
        let courseType        = $('input[name="course_type"]').val();
        let courseId          = $('input[name="course_id"]').val();
        let schemeId          = $('input[name="scheme_id"]').val();
        let branchId          = $('input[name="branch_id"]').val();
        let batchId           = $('input[name="batch_id"]').val();
        let annualSemesterId  = $('input[name="annual_semester_id"]').val();
        let instituteId       = $('input[name="institute_id"]').val();
        let marksType         = $('input[name="marks_type"]').val();
        let moderationAction = $('select[name="moderation_action"]').val();

        /*
        ==========================================
        CURRENT SUBJECT
        ==========================================
        */

        let subjectId = $('input[name="subject_id"]').val();

        /*
        ==========================================
        STUDENTS
        ==========================================
        */

        let moderationStudents = $('#moderation_students').val();

        /*
        ==========================================
        EXCLUDED STUDENTS
        ==========================================
        */

        let excludedStudents =
            $('input[name="exculded_students"]').val();

        /*
        ==========================================
        MODERATION MARKS
        ==========================================
        */

        let moderationMarks =
            $('input[name="moderation_marks"]').val();


        /*
        ==========================================
        VALIDATION
        ==========================================
        */

        if (!subjectId) {

            showToast(
                'Subject not found',
                'error'
            );

            return;
        }


        if (!moderationStudents) {

            showToast(
                'No students found for moderation',
                'error'
            );

            return;
        }


        if (!moderationMarks || moderationMarks === '') {

            showToast(
                'Please enter moderation marks',
                'error'
            );

            return;
        }


        if (isNaN(moderationMarks)) {

            showToast(
                'Moderation marks must be numeric',
                'error'
            );

            return;
        }


        moderationMarks = parseFloat(moderationMarks);


        /*
        ==========================================
        ANNUAL PER SUBJECT LIMIT
        ==========================================
        */

        if (moderationMarks <= 0) {

            showToast(
                'Moderation marks must be greater than 0',
                'error'
            );

            return;
        }


        if (moderationMarks > 3) {

            showToast(
                'Maximum 3 moderation marks allowed per subject in Annual Based course',
                'error'
            );

            return;
        }


        /*
        ==========================================
        AJAX
        ==========================================
        */

        $.ajax({

            url: "{{ route('results.save.annual.moderation') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                exam_id: examId,

                course_type: courseType,

                course_id: courseId,

                scheme_id: schemeId,

                branch_id: branchId,

                batch_id: batchId,

                annual_semester_id: annualSemesterId,

                institute_id: instituteId,

                marks_type: marksType,

                subject_id: subjectId,

                moderation_students: moderationStudents,

                excluded_students: excludedStudents,

                moderation_marks: moderationMarks,

                moderation_action : moderationAction
            },


            /*
            ==========================================
            BEFORE SEND
            ==========================================
            */

            beforeSend: function () {

                button.prop('disabled', true);

                button
                    .removeClass('btn-primary')
                    .addClass('btn-secondary');

                button.html('Please Wait...');
            },


            /*
            ==========================================
            SUCCESS
            ==========================================
            */

            success: function (response) {

                showToast(
                    response.message,
                    'success'
                );

                button.prop('disabled', false);

                button
                    .removeClass('btn-secondary')
                    .addClass('btn-primary');

                button.html('Apply');
            },


            /*
            ==========================================
            ERROR
            ==========================================
            */

            error: function (xhr) {

                let message =
                    'Something went wrong';


                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {

                    message =
                        xhr.responseJSON.message;
                }


                showToast(
                    message,
                    'error'
                );


                button.prop('disabled', false);

                button
                    .removeClass('btn-secondary')
                    .addClass('btn-primary');

                button.html('Apply');
            }

        });

    });

});
</script>
