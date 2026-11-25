<script>

$(document).ready(function () {

    /*
    ==========================================
    FORM VALIDATION
    ==========================================
    */

    let validator = $("#suppleSubjectOffcanvasTop form#assignSubjectForm").validate({

        rules: {

            // student_roll_number: {
            //     required: true
            // },

            exam_id: { required: true },

            course_type: { required: true },

            course_id: { required: true },

            branch_id: { required: true },

            batch_id: { required: true },

            institute_id: {
                required: true
            },

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

            // student_roll_number: "Enter student roll number",

            exam_id: "Please select exam",

            course_type: "Select course type",

            course_id: "Select course",

            branch_id: "Select branch",

            batch_id: "Select batch",

            institute_id: "Select institute",

            // subject_id: "Select subject",

            semester_id: "Select semester",

            annual_semester_id: "Select annual semester"

        },

        errorPlacement: function (error, element) {

            // checkbox group error handling
            if (element.attr("name") === "semester_id[]") {
                error.appendTo("#semester_container");
            }
            else if (element.attr("name") === "annual_semester_id[]") {
                error.appendTo("#annual_semester_container");
            }
            else {
                error.insertAfter(element);
            }

        }

    });

    /*
    ==========================================
    FUNCTION : LOAD INTERNAL MARKS
    ==========================================
    */

    function loadEndSemMarks()
    {

        let formData = $("#assignSubjectForm").serialize();

        // console.log(formData); return false;

        $.ajax({

            url: "{{ route('results.reappear-assign-subjects.fetch') }}",

            type: "GET",

            data: formData,

            beforeSend: function ()
            {

                $("#resultSheet").html(

                    '<div class="text-center p-5">' +

                    '<span class="spinner-border spinner-border-sm"></span> ' +

                    'Loading Transcript...' +

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

                    'Failed to load transcript. Please try again.' +

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
