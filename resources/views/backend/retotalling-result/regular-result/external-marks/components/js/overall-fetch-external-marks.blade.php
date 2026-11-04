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
            },
            institute_id: { required: true }
        },

        messages: {
            exam_id: "Please select exam",
            course_type: "Select course type",
            course_id: "Select course",
            branch_id: "Select branch",
            batch_id: "Select batch",
            semester_id: "Select semester",
            annual_semester_id: "Select annual semester",
            institute_id: "Select institute"
        }
    });

    function loadExternalMarks() {

        const formData = $("#searchExternalMarksForm").serialize();

        $.ajax({
            url: "{{ route('retotal-result.regular.external-marks.fetch') }}",
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

});

</script>
