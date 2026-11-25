<script>

$(document).ready(function () {

    /*
    ==========================================
    FORM VALIDATION
    ==========================================
    */

    let validator = $("#searchMappedSubjectOffcanvasTop form#searchMappedSubjectForm").validate({

        rules: {

            exam_id: { required: true },

            course_type: { required: true },

            result_type: { required: true },

            course_id: { required: true },

            scheme_id: { required: true },

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

            scheme_id: "Select scheme",

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

        let formData = $("#searchMappedSubjectForm").serialize();

        // console.log(formData); return false;

        $.ajax({

            url: "{{ route('retotal-result.mapped-subjects.fetch-students-mapped-subject-list') }}",

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


$(document).on('click', '.delete-mapped-subject', function () {

    let button = $(this);

    let id = button.data('id');

    Swal.fire({

        title: 'Are you sure?',

        text: 'This student mapped subjects record will be permanently deleted!',

        icon: 'warning',

        showCancelButton: true,

        confirmButtonColor: '#d33',

        cancelButtonColor: '#6c757d',

        confirmButtonText: 'Yes, Delete',

        cancelButtonText: 'Cancel'

    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({

                url: "{{ route('retotal-result.mapped-subjects.delete', ':id') }}"
                    .replace(':id', id),

                type: "DELETE",

                data: {
                    _token: "{{ csrf_token() }}"
                },

                beforeSend: function () {

                    button.prop('disabled', true);

                },

                success: function (response) {

                    if (response.status) {

                        // Remove deleted row
                        $('#mapped-row-' + id).fadeOut(300, function () {

                            $(this).remove();

                            // Update total count
                            let total = $('.mapped-subject-table tbody tr').length;

                            if (total === 0) {

                                $('#resultSheet').html(
                                    '<div class="alert alert-warning text-center">' +
                                    'No mapped student records found.' +
                                    '</div>'
                                );

                            } else {

                                $('.mapped-total-count').text(total);

                            }

                        });

                        Swal.fire({

                            icon: 'success',

                            title: 'Deleted!',

                            text: response.message,

                            timer: 1800,

                            showConfirmButton: false

                        });

                    } else {

                        Swal.fire(
                            'Error!',
                            response.message,
                            'error'
                        );

                        button.prop('disabled', false);

                    }

                },

                error: function (xhr) {

                    let message = xhr.responseJSON?.message ||
                        'Unable to delete record. Please try again.';

                    Swal.fire(
                        'Error!',
                        message,
                        'error'
                    );

                    button.prop('disabled', false);

                }

            });

        }

    });

});

</script>

