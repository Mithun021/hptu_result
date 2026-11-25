<script>

    function loadEndSemMarks()
    {

        let formData = $("#endSemMarksForm").serialize();

        // console.log(formData); return false;

        $.ajax({

            url: "{{ route('results.supple-result-prepare.fetch') }}",

            type: "GET",

            data: formData,

            beforeSend: function ()
            {

                $("#endSemMarksResult").html(

                    '<div class="text-center p-5">' +

                    '<span class="spinner-border spinner-border-sm"></span> ' +

                    'Loading Internal Marks...' +

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

                    'Failed to load internal marks. Please try again.' +

                    '</div>'

                );

            }

        });

    }

$(document).ready(function () {

    /*
    ==========================================
    FORM VALIDATION
    ==========================================
    */

    let validator = $("#endSemOffcanvasTop form#endSemMarksForm").validate({

        rules: {

            exam_id: { required: true },

            // midsem_exam_id: { required: true },

            course_type: { required: true },

            course_id: { required: true },

            branch_id: { required: true },

            batch_id: { required: true },

            // marks_type: { required: true },

            subject_id: { required: true },

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

            // midsem_exam_id: "Please select Mid-Sem exam",

            course_type: "Select course type",

            course_id: "Select course",

            branch_id: "Select branch",

            batch_id: "Select batch",

            semester_id: "Select semester",

            annual_semester_id: "Select annual semester",

            // marks_type: "Select marks type",

            subject_id: "Select subject",

        }

    });

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

<script>

$(document).on("click","#update-lock-status",function(){

    let status=$("#mark_status").val();

    if(status==""){
        Swal.fire("Please select Lock/Unlock status.");
        return;
    }

    let students=[];

    $(".student-check:checked").each(function(){

        students.push($(this).val());

    });

    if(students.length==0){

        Swal.fire("Please select students.");

        return;

    }

    Swal.fire({

        title:"Are you sure?",

        text:"Update selected students?",

        icon:"question",

        showCancelButton:true,

        confirmButtonText:"Yes"

    }).then((result)=>{

        if(!result.isConfirmed){
            return;
        }

        $.ajax({

            url:"{{ route('results.supplementary-result.update-lock-status') }}",

            type:"POST",

            data:{

                _token:"{{ csrf_token() }}",

                student_ids:students,

                is_locked:status,

                exam_id:$("input[name=exam_id]").val(),

                course_type:$("input[name=course_type]").val(),

                course_id:$("input[name=course_id]").val(),

                scheme_id:$("input[name=scheme_id]").val(),

                branch_id:$("input[name=branch_id]").val(),

                semester_id:$("input[name=semester_id]").val(),

                annual_semester_id:$("input[name=annual_semester_id]").val(),

                batch_id:$("input[name=batch_id]").val(),

                institute_id:$("input[name=institute_id]").val(),

                subject_id:$("input[name=subject_id]").val()

            },

            success:function(res){

                Toastify({

                    text:res.message,

                    duration:2500,

                    gravity:"top",

                    position:"right",

                    style:{
                        background:"#28a745"
                    }

                }).showToast();

                // Reload current sheet
                loadEndSemMarks();

            },

            error:function(){

                Toastify({

                    text:"Something went wrong",

                    duration:2500,

                    gravity:"top",

                    position:"right",

                    style:{
                        background:"#dc3545"
                    }

                }).showToast();

            }

        });

    });

});

</script>
