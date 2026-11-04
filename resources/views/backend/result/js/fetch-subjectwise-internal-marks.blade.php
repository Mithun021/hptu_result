<script src="{{ asset('assets/toastify/toastify-js') }}"></script>
<script>

$(document).ready(function () {

    /*
    ==========================================
    FORM VALIDATION
    ==========================================
    */

    let validator = $("#subjectwiseOffcanvasTop form#searchIndividualInternalMarksForm").validate({

        rules: {

            exam_id: { required: true },

            midsem_exam_id: { required: true },

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

            midsem_exam_id: "Please select Mid-Sem exam",

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

    function loadInternalMarks()
    {

        let formData = $("#searchIndividualInternalMarksForm").serialize();

        // console.log(formData); return false;

        $.ajax({

            url: "{{ route('results.subject-wise.internal-marks.fetch') }}",

            type: "GET",

            data: formData,

            beforeSend: function ()
            {

                $("#subjectwiseInternalMarksResult").html(

                    '<div class="text-center p-5">' +

                    '<span class="spinner-border spinner-border-sm"></span> ' +

                    'Loading Internal Marks...' +

                    '</div>'

                );

            },

            success: function (response)
            {

                $("#subjectwiseInternalMarksResult").html(response);

            },

            error: function ()
            {

                $("#subjectwiseInternalMarksResult").html(

                    '<div class="alert alert-danger text-center">' +

                    'Failed to load internal marks. Please try again.' +

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

        loadInternalMarks();

    });



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



});

</script>

<script>

$(document).ready(function () {

    /*
    ==========================================
    FORM VALIDATION
    ==========================================
    */

    let validator = $("#subjectwiseOffcanvasTop form#searchIndividualInternalMarksForm").validate({

        rules: {

            exam_id: { required: true },

            midsem_exam_id: { required: true },

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

            midsem_exam_id: "Please select Mid-Sem exam",

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
    LOAD INTERNAL MARKS
    ==========================================
    */

    function loadInternalMarks()
    {
        let formData = $("#searchIndividualInternalMarksForm").serialize();

        $.ajax({

            url: "{{ route('results.subject-wise.internal-marks.fetch') }}",

            type: "GET",

            data: formData,

            beforeSend: function ()
            {

                $("#subjectwiseInternalMarksResult").html(

                    '<div class="text-center p-5">' +
                    '<span class="spinner-border spinner-border-sm"></span> ' +
                    'Loading Internal Marks...' +
                    '</div>'

                );

            },

            success: function (response)
            {

                $("#subjectwiseInternalMarksResult").html(response);

            },

            error: function ()
            {

                $("#subjectwiseInternalMarksResult").html(

                    '<div class="alert alert-danger text-center">' +
                    'Failed to load internal marks. Please try again.' +
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

        loadInternalMarks();

    });

    /*
    ==========================================
    CHECK ALL
    ==========================================
    */

    $(document).on("change", "#check-all", function () {

        $(".lock-check").prop(
            "checked",
            $(this).is(":checked")
        );

    });

    
    /*
    ==========================================
    LOCK / UNLOCK UPDATE
    ==========================================
    */

    $(document).on("click", "#update-lock-status", function () {

        let markStatus = $("#mark_status").val();

        if (markStatus == "") {

            alert("Please select bulk action");
            return;
        }

        let records = [];

        $(".lock-check:checked").each(function () {

            records.push({

                student_id: $(this).data("student"),
                subject_id: $(this).data("subject"),
                marks_type: $(this).data("marks-type"),

                course_type: $(this).data("course-type"),
                course_id: $(this).data("course-id"),
                branch_id: $(this).data("branch-id"),
                batch_id: $(this).data("batch-id"),
                scheme_id: $(this).data("scheme-id"),

                semester_id: $(this).data("semester-id"),
                annual_semester_id: $(this).data("annual-semester-id"),

                exam_id: $(this).data("exam-id"),
                mid_sem_exam_id: $(this).data("midsem-exam-id"),

                is_locked: markStatus

            });

        });

        if (records.length === 0) {

            alert("Please select at least one student");
            return;
        }

        $.ajax({

            url: "{{ route('results.internal-marks.lock-unlock') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",
                records: records

            },

            beforeSend: function () {

                $("#update-lock-status")
                    .prop("disabled", true)
                    .text("Updating...");
            },

            success: function (response) {

                alert(response.message);

                loadInternalMarks();

            },

            error: function () {

                alert("Something went wrong");

            },

            complete: function () {

                $("#update-lock-status")
                    .prop("disabled", false)
                    .text("Update Status");
            }

        });

    });


    /*
    ==========================================
    GRACE MARKS SAVE / UPDATE
    ==========================================
    */

    $(document).on("change", ".grace-marks-input", function () {

        let input = $(this);

        let graceMarks = input.val();

        $.ajax({

            url: "{{ route('results.internal-marks.grace-marks') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                records: [{

                    student_id: input.data("student"),
                    subject_id: input.data("subject"),
                    marks_type: input.data("marks-type"),

                    course_type: input.data("course-type"),
                    course_id: input.data("course-id"),
                    branch_id: input.data("branch-id"),
                    batch_id: input.data("batch-id"),
                    scheme_id: input.data("scheme-id"),

                    semester_id: input.data("semester-id"),
                    annual_semester_id: input.data("annual-semester-id"),

                    exam_id: input.data("exam-id"),
                    mid_sem_exam_id: input.data("midsem-exam-id"),

                    grace_marks: graceMarks

                }]

            },

            success: function (response) {

                console.log(response.message);

            },

            error: function () {

                alert("Failed to update grace marks");

            }

        });

    });
    
    /*
    ==========================================
    TOAST
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
    SAVE MODERATION
    ==========================================
    */

    $(document).on("click", ".save-moderation", function () {
        // alert("This feature is currently disabled for maintenance. Please contact administrator.");
        // return;
        let button = $(this);

        let td = button.closest("td");

        let moderationMarks =
            td.find(".moderation-marks-input").val();

        let adjustmentType =
            td.find(".moderation-type").val();

        $.ajax({

            url: "{{ route('results.save.academic.adjustment') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                exam_id: button.data("exam"),

                student_id: button.data("student"),
                subject_id: button.data("subject"),

                institute_id: $("#institute_id").val(),

                course_type: $("#course_type").val(),

                course_id: $("#course_id").val(),
                branch_id: $("#branch_id").val(),
                batch_id: $("#batch_id").val(),

                semester_id: $("#semester_id").val(),
                annual_semester_id: $("#annual_semester_id").val(),

                scheme_id: $("#scheme_id").val(),

                adjustment_marks: moderationMarks,

                adjustment_type: adjustmentType,

                adjustment_scope:
                    button.data("adjustment-scope"),

                marks_type: button.data("marks-type")

            },

            beforeSend: function () {

                button.prop("disabled", true);

                button.removeClass("btn-primary")
                    .addClass("btn-secondary");

                button.html("Saving...");
            },

            success: function (response) {

                showToast(
                    response.message,
                    "success"
                );

                button.prop("disabled", false);

                button.removeClass("btn-secondary")
                    .addClass("btn-primary");

                button.html("Save");
            },

            error: function (xhr) {

                let message = "Something went wrong";

                if(xhr.responseJSON &&
                    xhr.responseJSON.message)
                {
                    message = xhr.responseJSON.message;
                }

                showToast(message, "error");

                button.prop("disabled", false);

                button.removeClass("btn-secondary")
                    .addClass("btn-primary");

                button.html("Save");
            }

        });

    });


});

</script>