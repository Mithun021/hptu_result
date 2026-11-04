<script>
$(document).ready(function () {

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
    FORM VALIDATION
    =====================================================
    */

    const validator = $("#externalmarksoffcanvasTop form#searchExternalMarksForm").validate({
        rules: {
            exam_id: { required: true },
            course_type: { required: true },
            course_id: { required: true },
            branch_id: { required: true },
            batch_id: { required: true },

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
            annual_semester_id: "Select annual semester"
        }
    });


    /*
    =====================================================
    LOAD EXTERNAL MARKS
    =====================================================
    */

    function loadExternalMarks() {

        const formData = $("#searchExternalMarksForm").serialize();

        $.ajax({
            url: "{{ route('results.external-marks.fetch') }}",
            type: "GET",
            data: formData,

            beforeSend: function () {
                $("#externalMarksResult").html(`
                    <div class="text-center p-5">
                        <span class="spinner-border spinner-border-sm"></span>
                        Loading External Marks...
                    </div>
                `);
            },

            success: function (response) {
                $("#externalMarksResult").html(response);
            },

            error: function () {
                $("#externalMarksResult").html(`
                    <div class="alert alert-danger text-center">
                        Failed to load external marks. Please try again.
                    </div>
                `);
            }
        });
    }


    /*
    =====================================================
    APPLY FILTER
    =====================================================
    */

    $("#applyFilter").on("click", function () {

        if (!validator.form()) return;

        loadExternalMarks();
    });


    /*
    =====================================================
    SAVE MARKS ON INPUT CHANGE
    =====================================================
    */

    $(document).on("change", ".save-marks", function () {

        const input = $(this);

        const studentId = input.data("student");
        const subjectId = input.data("subject");
        const marks     = input.val();

        const cell = input.closest("td");
        const absentCheckbox = cell.find(".mark-absent");

        const status = absentCheckbox.is(":checked") ? "absent" : "present";

        saveExternalMarks(studentId, subjectId, marks, status, input);
    });


    /*
    =====================================================
    ABSENT CHECKBOX CHANGE
    =====================================================
    */

    $(document).on("change", ".mark-absent", function () {

        const checkbox = $(this);

        const studentId = checkbox.data("student");
        const subjectId = checkbox.data("subject");

        const cell = checkbox.closest("td");
        const input = cell.find(".save-marks");

        const marks  = input.val();
        const status = checkbox.is(":checked") ? "absent" : "present";

        saveExternalMarks(studentId, subjectId, marks, status, input);
    });


    /*
    =====================================================
    SAVE MARKS FUNCTION
    =====================================================
    */

    function saveExternalMarks(studentId, subjectId, marks, status, input) {

        const maxMarks = parseFloat(input.data("max"));
        const marksType = input.attr("data-paper") || "external";

        if (marks !== "" && parseFloat(marks) > maxMarks) {

            // alert("Marks cannot be greater than Max Marks (" + maxMarks + ")");
            showToast("Marks cannot be greater than Max Marks (" + maxMarks + ")", "error");

            input.val("");
            input.css("background", "#f8d7da");

            return;
        }

        const formData = $("#searchExternalMarksForm").serializeArray();
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

        // console.log(payload); return false;

        $.ajax({
            url: "{{ route('results.external-marks.save') }}",
            type: "POST",
            data: payload,

            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },

            beforeSend: function () {
                input.css("background", "#fff3cd"); // saving
            },

            success: function () {

                input.css("background", "#d4edda"); // success

                showToast("Marks saved successfully", "success");

                setTimeout(function () {
                    input.css("background", "");
                }, 800);
            },

            error: function () {
                input.css("background", "#f8d7da"); // error
                showToast("Failed to save marks", "error");
            }
        });
    }


    /*
    =====================================================
    ROW CHECKBOX → SELECT WHOLE ROW
    =====================================================
    */

    $(document).on("change", ".row-check", function () {

        const studentId = $(this).data("student");
        const checked   = $(this).is(":checked");

        $(".lock-check[data-student='" + studentId + "']")
            .prop("checked", checked);
    });


    /*
    =====================================================
    LOCK / UNLOCK MARKS
    =====================================================
    */

    $("#mark_status").on("change", function () {

        const status = $(this).val();

        if (status === "") return;

        let selected = [];

        $(".lock-check:checked").each(function () {

            selected.push({
                student_id: $(this).data("student"),
                subject_id: $(this).data("subject")
            });

        });

        if (selected.length === 0) {
            // alert("Please select at least one checkbox");
            showToast("Please select at least one checkbox", "warning");
            $(this).val("");
            return;
        }

        const formData = $("#searchExternalMarksForm").serializeArray();
        const payload  = {};

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

                /*
                AUTO REFRESH RESULT TABLE
                */

                loadExternalMarks();
            },

            error: function () {

                $("#mark_status").prop("disabled", false);

                // alert("Update failed");
                showToast("Update failed", "error");
            }
        });

    });

});

$(document).on("change", ".grade-radio", function () {

    const radio = $(this);

    const studentId = radio.attr("name").split('_')[1];
    const subjectId = radio.attr("name").split('_')[2];
    const grade     = radio.val();

    const formData = $("#searchExternalMarksForm").serializeArray();
    const payload = {};

    formData.forEach(function (item) {
        payload[item.name] = item.value;
    });

    payload.student_id = studentId;
    payload.subject_id = subjectId;

    payload.marks = grade;        // ✅ FIXED (was grade)
    payload.status = "present";   // ✅ FIXED (added)

    payload.marks_type = "external"; 
    payload.is_nc      = 1;

    $.ajax({
        url: "{{ route('results.external-marks.save') }}",
        type: "POST",
        data: payload,

        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },

        beforeSend: function () {
            radio.closest("td").css("background", "#fff3cd");
        },

        success: function () {
            radio.closest("td").css("background", "#d4edda");
            showToast("Grade saved successfully", "success");

            setTimeout(function () {
                radio.closest("td").css("background", "");
            }, 800);
        },

        error: function () {
            radio.closest("td").css("background", "#f8d7da");
            showToast("Failed to save grade", "error");
        }
    });

});
</script>