<script>

$(document).ready(function () {

    /*
    ==========================================
    FORM VALIDATION
    ==========================================
    */

    let validator = $("#examinationOffcanvasTop form#examFormSearchForm").validate({

        rules: {

            exam_id: { required: true },

            // course_type: { required: true },

            // course_id: { required: true },

            // branch_id: { required: true },

            // batch_id: { required: true },

            // institute_id: { required: true },

            // semester_id: {
            //     required: function () {
            //         return $("#course_type").val() === "Semester Based";
            //     }
            // },

            // annual_semester_id: {
            //     required: function () {
            //         return $("#course_type").val() === "Annual Based";
            //     }
            // }

        },

        messages: {

            exam_id: "Please select exam",

            // course_type: "Select course type",

            // course_id: "Select course",

            // branch_id: "Select branch",

            // batch_id: "Select batch",

            // semester_id: "Select semester",

            // annual_semester_id: "Select annual semester",

            // institute_id : "Select institute",

        }

    });


    /*
    |--------------------------------------------------------------------------
    | FUNCTION : LOAD EXAMINATION FORMS
    |--------------------------------------------------------------------------
    */

    function loadExamForms()
    {
        let formData = $("#examFormSearchForm").serialize();

        $.ajax({

            url: "{{ route('admin.examination-form.fetch') }}",

            type: "GET",

            data: formData,

            beforeSend: function ()
            {
                $("#examSheet").html(
                    '<div class="text-center p-5">' +
                    '<span class="spinner-border spinner-border-sm"></span> ' +
                    'Loading Examination Forms...' +
                    '</div>'
                );
            },

            success: function (response)
            {
                $("#examSheet").html(response);
            },

            error: function (xhr)
            {
                let message = 'Failed to load Examination Forms. Please try again.';

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    message = Object.values(xhr.responseJSON.errors)
                        .flat()
                        .join('<br>');
                }

                $("#examSheet").html(
                    '<div class="alert alert-danger text-center">' +
                    message +
                    '</div>'
                );
            }

        });
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER BUTTON CLICK
    |--------------------------------------------------------------------------
    */

    $("#applyFilter").on("click", function () {

        if (!validator.form()) {
            return;
        }

        loadExamForms();

    });


});

</script>

