<script>

$(document).ready(function () {

    /*
    ==========================================
    FORM VALIDATION
    ==========================================
    */

    let validator = $("#endSemOffcanvasTop form#endSemMarksForm").validate({

        rules: {

            exam_id: { required: true },

            course_type: { required: true },

            course_id: { required: true },

            scheme_id: { required: true },

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

            scheme_id: "Select scheme",

            branch_id: "Select branch",

            batch_id: "Select batch",

            semester_id: "Select semester",

            annual_semester_id: "Select annual semester",

        }

    });



    /*
    ==========================================
    FUNCTION : LOAD INTERNAL MARKS
    ==========================================
    */

    function loadEndSemMarks()
    {

        let formData = $("#endSemMarksForm").serialize();

        // console.log(formData); return false;

        $.ajax({

            url: "{{ route('results.reappear.fetch-overall-result') }}",

            type: "GET",

            data: formData,

            beforeSend: function ()
            {

                $("#endSemMarksResult").html(

                    '<div class="text-center p-5">' +

                    '<span class="spinner-border spinner-border-sm"></span> ' +

                    'Loading Supple Marks...' +

                    '</div>'

                );

            },

            success: function (response)
            {

                $("#endSemMarksResult").html(response);

            },

            error: function ()
            {

                $("#endSemMarksResult").html(

                    '<div class="alert alert-danger text-center">' +

                    'Failed to load end-sem marks. Please try again.' +

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

        loadEndSemMarks();

    });

});

</script>
