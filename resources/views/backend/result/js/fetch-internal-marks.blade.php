<script>

$(document).ready(function () {

    /*
    ==========================================
    FORM VALIDATION
    ==========================================
    */

    let validator = $("#offcanvasTop form#searchInternalMarksForm").validate({

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
    ==========================================
    FUNCTION : LOAD INTERNAL MARKS
    ==========================================
    */

    function loadInternalMarks()
    {

        let formData = $("#searchInternalMarksForm").serialize();

        $.ajax({

            url: "{{ route('results.internal-marks.fetch') }}",

            type: "GET",

            data: formData,

            beforeSend: function ()
            {

                $("#internalMarksResult").html(

                    '<div class="text-center p-5">' +

                    '<span class="spinner-border spinner-border-sm"></span> ' +

                    'Loading Internal Marks...' +

                    '</div>'

                );

            },

            success: function (response)
            {

                $("#internalMarksResult").html(response);

            },

            error: function ()
            {

                $("#internalMarksResult").html(

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

});

</script>