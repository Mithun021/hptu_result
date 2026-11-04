<script>
    // 🔴 FULL CHANGE SCRIPT
    document.getElementById('course_type').addEventListener('change', updateMarksType);
    document.getElementById('course_id').addEventListener('change', updateMarksType);

    function updateMarksType() {

        let courseType = document.getElementById('course_type').value;
        let courseSelect = document.getElementById('course_id');
        let selectedOption = courseSelect.options[courseSelect.selectedIndex];

        let courseCategory = selectedOption.getAttribute('data-course'); // UG / Diploma

        let marksType = document.getElementById('marks_type');

        // reset
        marksType.innerHTML = '<option value="">--Select--</option>';

        if (!courseType || !courseCategory) return;

        /* =========================
        SEMESTER BASED
        ==========================*/
        if (courseType === 'Semester Based') {

            // UG
            if (courseCategory === 'UG') {
                marksType.innerHTML += `
                    <option value="Mid-Sem">Mid-Sem</option>
                    <option value="Quiz">Quiz</option>
                    <option value="Practical">Practical</option>
                `;
            }

            // Diploma
            if (courseCategory === 'Diploma') {
                marksType.innerHTML += `
                    <option value="Mid-Sem">Mid-Sem</option>
                `;
            }
        }

        /* =========================
        ANNUAL BASED
        ==========================*/
        if (courseType === 'Annual Based') {

            // UG
            if (courseCategory === 'UG') {
                marksType.innerHTML += `
                    <option value="Internal 1">Internal 1</option>
                    <option value="Internal 2">Internal 2</option>
                    <option value="Internal 3">Internal 3</option>
                `;
            }

            // Diploma
            if (courseCategory === 'Diploma') {
                marksType.innerHTML += `
                    <option value="Internal 1">Internal 1</option>
                    <option value="Internal 2">Internal 2</option>
                    <option value="Internal 3">Internal 3</option>
                `;
            }
        }
    }
</script>

<script>

$("#exam_id").change(function(){

    var exam_id = $(this).val();

    if(exam_id != ''){

        $.ajax({

            url: "/get-midsem-exams/" + exam_id,
            type: "GET",

            beforeSend: function(){

                $("#midsem_exam_id").html('<option value="">Please wait...</option>');

            },

            success: function(data){

                var html = '<option value="">--Select--</option>';

                $.each(data, function(key, value){

                    html += '<option value="'+value.id+'">'+value.mid_sem_exam_name+'</option>';

                });

                $("#midsem_exam_id").html(html);

            }

        });

    }
    else{

        $("#midsem_exam_id").html('<option value="">--Select--</option>');

    }

});

</script>

<script>
$(document).ready(function () {

    function resetSemesterUI() {
        $('#semester_container').hide();
        $('#annual_semester_container').hide();

        $('#semester_id').html('<option value="">--Select Semester--</option>');
        $('#annual_semester_id').html('<option value="">--Select--</option>');
    }

    function loadSubjects() {

        let courseType = $('#course_type').val();
        let courseId   = $('#course_id').val();
        let schemeId   = $('#scheme_id').val();
        let branchId   = $('#branch_id').val();
        let semesterId = $('#semester_id').val();
        let annualSemesterId = $('#annual_semester_id').val();

        if (!courseId || !branchId) {
            return;
        }

        $('#subject_id').html('<option>Please wait...</option>');

        $.ajax({
            url: "{{ url('result/subjects/filter') }}",
            type: "GET",
            data: {
                course_type: courseType,
                course_id: courseId,
                scheme_id: schemeId,
                branch_id: branchId,
                semester_id: semesterId,
                annual_semester_id: annualSemesterId
            },
            success: function (subjects) {

                $('#subject_id').html('<option value="">--Select Subject--</option>');

                if (subjects.length > 0) {
                    $.each(subjects, function (_, subject) {
                        $('#subject_id').append(
                            `<option value="${subject.id}">${subject.name}</option>`
                        );
                    });
                } else {
                    $('#subject_id').append(
                        `<option value="">No Subject Found</option>`
                    );
                }
            }
        });
    }

    $('#branch_id, #scheme_id, #semester_id, #annual_semester_id').on('change', function () {
        loadSubjects();
    });

    function loadCourseData(courseId) {
        if (!courseId) return;

        resetSemesterUI();

        /* =========================
           LOADERS
        ==========================*/
        $('#branch_id').html('<option>Please wait...</option>');
        $('#scheme_id').html('<option>Please wait...</option>');
        $('#batch_id').html('<option>Please wait...</option>');
        $('#institute_id').html('<option>Please wait...</option>');

        /* =========================
           FETCH BRANCHES
        ==========================*/
        $.get("{{ url('branches') }}/" + courseId, function (branches) {
            $('#branch_id').html('<option value="">--Select Branch--</option>');
            $.each(branches, function (_, branch) {
                $('#branch_id').append(
                    `<option value="${branch.id}">${branch.name}</option>`
                );
            });
        });

        /* =========================
           FETCH COURSE DETAILS
        ==========================*/
        $.get("{{ url('courses') }}/" + courseId + "/details", function (response) {

            /* ---------- Schemes ---------- */
            $('#scheme_id').html('<option value="">--Select Scheme--</option>');
            $.each(response.schemes, function (_, scheme) {
                $('#scheme_id').append(
                    `<option value="${scheme.id}">${scheme.name}</option>`
                );
            });

            /* ---------- Batches ---------- */
            $('#batch_id').html('<option value="">--Select Batch--</option>');
            $.each(response.batches, function (_, batch) {
                $('#batch_id').append(
                    `<option value="${batch.id}">${batch.name}</option>`
                );
            });

            /* ---------- Institutes ---------- */
            $('#institute_id').html('<option value="">--Select Institute--</option>');
            $.each(response.institutes, function (_, institute) {
                $('#institute_id').append(
                    `<option value="${institute.id}">${institute.name}</option>`
                );
            });

            let courseType = $('#course_type').val();

            /* =========================
               SEMESTER BASED
            ==========================*/
            if (courseType === 'Semester Based') {
                $('#semester_container').show();
                $('#annual_semester_container').hide();

                $('#semester_id').html('<option value="">--Select Semester--</option>');
                $.each(response.semesters, function (_, semester) {
                    $('#semester_id').append(
                        `<option value="${semester.id}">${semester.name}</option>`
                    );
                });
            }

            /* =========================
               ANNUAL BASED
            ==========================*/
            if (courseType === 'Annual Based') {
                $('#annual_semester_container').show();
                $('#semester_container').hide();

                $('#annual_semester_id').html('<option value="">--Select--</option>');
                $.each(response.annual_semesters, function (_, annual) {
                    $('#annual_semester_id').append(
                        `<option value="${annual.id}">${annual.name}</option>`
                    );
                });
            }
        });
    }

    /* =========================
       COURSE CHANGE
    ==========================*/
    $('#course_id').on('change', function () {
        loadCourseData($(this).val());
    });

    /* =========================
       COURSE TYPE CHANGE
    ==========================*/
    $('#course_type').on('change', function () {
        resetSemesterUI();
        let courseId = $('#course_id').val();
        if (courseId) {
            loadCourseData(courseId);
        }
    });

    /* =========================
       AUTO LOAD (EDIT / OLD)
    ==========================*/
    let defaultCourse = $('#course_id').val();
    let defaultType   = $('#course_type').val();

    if (defaultCourse && defaultType) {
        loadCourseData(defaultCourse);
    }

});
</script>