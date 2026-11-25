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

            scheme_id: { required: true },

            branch_id: { required: true },

            batch_id: { required: true },

            marks_type: { required: true },

            subject_id: { required: true },

            result_type: { required: true },

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

            scheme_id: "Select scheme",

            branch_id: "Select branch",

            batch_id: "Select batch",

            semester_id: "Select semester",

            annual_semester_id: "Select annual semester",

            marks_type: "Select marks type",

            subject_id: "Select subject",

            result_type : "Select Result Type",

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

            url: "{{ route('retotal-result.regular.external-marks.subject-wise.fetch') }}",

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
    SAVE / UPDATE RETOTALLING MARKS
    =====================================================
    */

    $(document).off("change", ".retotalling-marks-input");

    $(document).on("change", ".retotalling-marks-input", function () {

        const input = $(this);

        const marks = input.val();

        const maxMarks = parseFloat(input.data("max"));

        console.log("Retotalling Save Event Triggered");

        console.log({
            student_id: input.data("student"),
            subject_id: input.data("subject"),
            exam_id: input.data("exam"),
            marks: marks,
            max_marks: maxMarks,
            marks_type: input.data("marks-type")
        });

        if (marks === "" || isNaN(parseFloat(marks))) {

            showToast("Please enter marks", "warning");
            return;
        }

        if (parseFloat(marks) < 0 || parseFloat(marks) > maxMarks) {

            showToast(
                "Marks cannot be greater than Max Marks (" + maxMarks + ")",
                "error"
            );

            input.css("background", "#f8d7da");

            return;
        }

        saveRetotallingMarks(input);

    });


    /*
    =====================================================
    SAVE FUNCTION
    =====================================================
    */

    function saveRetotallingMarks(input)
    {
        const formData = $("#searchIndividualExternalMarksForm")
            .serializeArray();

        const payload = {};

        formData.forEach(function (item) {
            payload[item.name] = item.value;
        });

        payload.student_id = input.data("student");
        payload.subject_id = input.data("subject");
        payload.exam_id = input.data("exam");

        payload.marks = input.val();
        payload.max_marks = input.data("max");
        payload.marks_type = input.data("marks-type");

        console.log("Sending Retotalling AJAX:", payload);

        $.ajax({

            url: "{{ route('retotal-result.regular.external-marks.save') }}",

            type: "POST",

            data: payload,

            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },

            beforeSend: function () {

                input.prop("disabled", true);
                input.css("background", "#fff3cd");

            },

            success: function (response) {

                input.prop("disabled", false);
                input.css("background", "#d4edda");

                showToast(
                    response.message || "Retotalling marks updated successfully.",
                    "success"
                );

                setTimeout(function () {
                    input.css("background", "");
                }, 800);

            },

            error: function (xhr) {

                input.prop("disabled", false);
                input.css("background", "#f8d7da");

                let message = "Failed to update retotalling marks.";

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                console.error("Retotalling Save Error:", xhr.responseJSON);

                showToast(message, "error");

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

    $("#mark_status").on("change", function () {

        const status = $(this).val();

        if (status === "") return;

        let selected = [];

        $(".lock-check:checked").each(function () {

            selected.push({

                student_id: $(this).data("student"),
                subject_id: $(this).data("subject"),
                exam_id: $(this).data("exam"),
                marks_type: $('#marksType').val()

            });

        });

        if (selected.length === 0) {

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
        payload.status = status;

        $.ajax({

            url: "{{ route('retotal-result.regular.external-marks.update-lock') }}",

            type: "POST",

            data: payload,

            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },

            beforeSend: function () {

                $("#mark_status").prop("disabled", true);

            },

            success: function (response) {

                $("#mark_status").prop("disabled", false);

                showToast(
                    response.message || "Lock status updated successfully",
                    "success"
                );

                loadExternalMarks();

            },

            error: function (xhr) {

                $("#mark_status").prop("disabled", false);

                showToast(
                    xhr.responseJSON?.message || "Update failed",
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

});
</script>
