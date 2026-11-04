<script>

$(document).ready(function () {

    /*
    ==========================================
    FORM VALIDATION
    ==========================================
    */

    let validator = $("#searchStudentOffcanvasTop form#searchStudentForm").validate({

        rules: {

            exam_id: { required: true },

            course_type: { required: true },

            course_id: { required: true },

            branch_id: { required: true },

            batch_id: { required: true },

            institute_id: { required: true },

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

            institute_id : "Select institute",

        }

    });

    /*
    ==========================================
    FUNCTION : LOAD INTERNAL MARKS
    ==========================================
    */

    function loadStudents()
    {

        let formData = $("#searchStudentForm").serialize();

        // console.log(formData); return false;

        $.ajax({

            url: "{{ route('retotal-result.mapped-subjects.fetch-students') }}",

            type: "GET",

            data: formData,

            beforeSend: function ()
            {

                $("#resultSheet").html(

                    '<div class="text-center p-5">' +

                    '<span class="spinner-border spinner-border-sm"></span> ' +

                    'Loading Student Detail...' +

                    '</div>'

                );

            },

            success: function (response)
            {

                $("#resultSheet").html(response);

            },

            error: function (xhr)
            {
                let errorMessage = 'Failed to load Student Detail. Please try again.';

                if (xhr.status === 422) {

                    let errors = xhr.responseJSON?.errors;

                    if (errors) {
                        errorMessage = Object.values(errors)
                            .flat()
                            .join('<br>');
                    } else {
                        errorMessage = xhr.responseJSON?.message || errorMessage;
                    }

                } else if (xhr.status === 404) {

                    errorMessage = 'Requested route or data not found.';

                } else if (xhr.status === 500) {

                    errorMessage = xhr.responseJSON?.message || 'Internal Server Error. Please contact administrator.';

                } else if (xhr.responseJSON?.message) {

                    errorMessage = xhr.responseJSON.message;

                }

                $("#resultSheet").html(
                    '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        '<i data-feather="alert-circle" class="me-1"></i>' +
                        '<strong>Error!</strong><br>' +
                        errorMessage +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>'
                );

                if (typeof feather !== 'undefined') {
                    feather.replace();
                }

                console.error('AJAX Error:', xhr.responseText);
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

        loadStudents();

    });

});

</script>

