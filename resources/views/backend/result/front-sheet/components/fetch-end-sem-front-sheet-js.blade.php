<script src="{{ asset('assets/toastify/toastify-js') }}"></script>
<script>

$(document).ready(function () {

    /*
    ==========================================
    FORM VALIDATION
    ==========================================
    */

    let validator = $("#senSemCourseOffcanvasTop form#searchEndSemCourseMarksForm").validate({

        rules: {

            exam_id: { required: true },

            course_type: { required: true },

            course_id: { required: true },

            branch_id: { required: true },

            batch_id: { required: true },

            institute_id: { required: true },

            // subject_id: { required: true },

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

            institute_id: "Select institute",

            // subject_id: "Select subject",

        }

    });



    /*
    ==========================================
    FUNCTION : LOAD END SEM FRONT SHEET
    ==========================================
    */

    function loadEndSemFrontSheet()
    {

        let formData = $("#searchEndSemCourseMarksForm").serialize();

        // console.log(formData); return false;

        $.ajax({

            url: "{{ route('results.frontsheet.end-sem-course.fetch') }}",

            type: "GET",

            data: formData,

            beforeSend: function ()
            {

                $("#resultSheet").html(

                    '<div class="text-center p-5">' +

                    '<span class="spinner-border spinner-border-sm"></span> ' +

                    'Loading End Sem Front Sheet...' +

                    '</div>'

                );

            },

            success: function (response)
            {

                $("#resultSheet").html(response);

            },

            error: function ()
            {

                $("#resultSheet").html(

                    '<div class="alert alert-danger text-center">' +

                    'Failed to load NC Course Sheet. Please try again.' +

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

        loadEndSemFrontSheet();

    });


});

</script>
