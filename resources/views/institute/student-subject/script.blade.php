<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

<script>

    // =========================
    // MARKS TYPE CHANGE
    // =========================
    document.getElementById('course_type').addEventListener('change', updateMarksType);
    document.getElementById('course_id').addEventListener('change', updateMarksType);

    function updateMarksType() {

        let courseType = document.getElementById('course_type').value;
        let courseSelect = document.getElementById('course_id');
        let selectedOption = courseSelect.options[courseSelect.selectedIndex];
        let courseCategory = selectedOption.getAttribute('data-course');

        let marksType = document.getElementById('marks_type');

        marksType.innerHTML = '<option value="">--Select--</option>';

        if (!courseType || !courseCategory) return;

        // Semester Based
        if (courseType === 'Semester Based') {

            if (courseCategory === 'UG') {
                marksType.innerHTML += `
                    <option value="Mid-Sem">Mid-Sem</option>
                    <option value="Quiz">Quiz</option>
                    <option value="Practical">Practical</option>
                `;
            }

            if (courseCategory === 'Diploma') {
                marksType.innerHTML += `
                    <option value="Mid-Sem">Mid-Sem</option>
                    <option value="Quiz">Quiz</option>
                `;
            }
        }

        // Annual Based
        if (courseType === 'Annual Based') {

            if (courseCategory === 'UG') {
                marksType.innerHTML += `
                    <option value="Internal 1">Internal 1</option>
                    <option value="Internal 2">Internal 2</option>
                    <option value="Internal 3">Internal 3</option>
                    <option value="Paper 1">Paper 1</option>
                    <option value="Paper 2">Paper 2</option>
                    <option value="Practical 1">Practical 1</option>
                    <option value="Practical 2">Practical 2</option>
                `;
            }

            if (courseCategory === 'Diploma') {
                marksType.innerHTML += `
                    <option value="Internal 1">Internal 1</option>
                    <option value="Internal 2">Internal 2</option>
                    <option value="Internal 3">Internal 3</option>
                    <option value="Paper 1">Paper 1</option>
                    <option value="Paper 2">Paper 2</option>
                `;
            }
        }
    }
</script>


<script>
$(document).ready(function () {

    // ==========================================
    // CONFIGURATION FOR BOTH SECTIONS
    // ==========================================
    const sections = [
        {
            prefix: '',
            courseType: '#course_type',
            courseId: '#course_id',
            schemeId: '#scheme_id',
            branchId: '#branch_id',
            semesterId: '#semester_id',
            annualSemesterId: '#annual_semester_id',
            semesterContainer: '#semester_container',
            annualSemesterContainer: '#annual_semester_container',
            batchId: '#batch_id',
            subjectId: '#subject_id'
        },
        {
            prefix: 'export_',
            courseType: '#export_course_type',
            courseId: '#export_course_id',
            schemeId: '#export_scheme_id',
            branchId: '#export_branch_id',
            semesterId: '#export_semester_id',
            annualSemesterId: '#export_annual_semester_id',
            semesterContainer: '#export_semester_container',
            annualSemesterContainer: '#export_annual_semester_container',
            batchId: '#export_batch_id',
            subjectId: '#export_subject_id'
        }
    ];

    // ==========================================
    // RESET SEMESTER UI
    // ==========================================
    function resetSemesterUI(section) {

        $(section.semesterContainer).hide();
        $(section.annualSemesterContainer).hide();

        $(section.semesterId).html(
            '<option value="">--Select Semester--</option>'
        );

        $(section.annualSemesterId).html(
            '<option value="">--Select Annual Semester--</option>'
        );
    }

    // ==========================================
    // LOAD SUBJECTS
    // ==========================================
    function loadSubjects(section) {

        let courseType = $(section.courseType).val();
        let courseId = $(section.courseId).val();
        let schemeId = $(section.schemeId).val();
        let branchId = $(section.branchId).val();
        let semesterId = $(section.semesterId).val();
        let annualSemesterId = $(section.annualSemesterId).val();

        if (!courseId || !branchId) {
            return;
        }

        // Subject dropdown is optional for sections
        // that do not contain a subject field.
        if (!$(section.subjectId).length) {
            return;
        }

        $(section.subjectId).html(
            '<option value="">Please wait...</option>'
        );

        $.ajax({
            url: "{{ url('institute/subjects/filter') }}",
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

                $(section.subjectId).html(
                    '<option value="">--Select Subject--</option>'
                );

                if (subjects && subjects.length > 0) {

                    $.each(subjects, function (_, subject) {

                        $(section.subjectId).append(
                            $('<option>', {
                                value: subject.id,
                                text: subject.name
                            })
                        );

                    });

                } else {

                    $(section.subjectId).append(
                        '<option value="">No Subject Found</option>'
                    );

                }
            },
            error: function () {

                $(section.subjectId).html(
                    '<option value="">Failed to load subjects</option>'
                );

            }
        });
    }

    // ==========================================
    // LOAD COURSE DATA
    // ==========================================
    function loadCourseData(section, courseId) {

        if (!courseId) {

            resetSemesterUI(section);

            $(section.branchId).html(
                '<option value="">--Select Branch--</option>'
            );

            $(section.schemeId).html(
                '<option value="">--Select Scheme--</option>'
            );

            $(section.batchId).html(
                '<option value="">--Select Batch--</option>'
            );

            if ($(section.subjectId).length) {
                $(section.subjectId).html(
                    '<option value="">--Select Subject--</option>'
                );
            }

            return;
        }

        resetSemesterUI(section);

        $(section.branchId).html(
            '<option value="">Please wait...</option>'
        );

        $(section.schemeId).html(
            '<option value="">Please wait...</option>'
        );

        $(section.batchId).html(
            '<option value="">Please wait...</option>'
        );

        // ======================================
        // LOAD BRANCHES
        // ======================================
        $.get(
            "{{ url('institute/branches') }}/" + courseId,
            function (branches) {

                $(section.branchId).html(
                    '<option value="">--Select Branch--</option>'
                );

                $.each(branches, function (_, branch) {

                    $(section.branchId).append(
                        $('<option>', {
                            value: branch.id,
                            text: branch.name
                        })
                    );

                });

            }
        ).fail(function () {

            $(section.branchId).html(
                '<option value="">Failed to load branches</option>'
            );

        });

        // ======================================
        // LOAD SCHEMES, BATCHES AND SEMESTERS
        // ======================================
        $.get(
            "{{ url('courses') }}/" + courseId + "/details",
            function (response) {

                // SCHEMES
                $(section.schemeId).html(
                    '<option value="">--Select Scheme--</option>'
                );

                $.each(response.schemes || [], function (_, scheme) {

                    $(section.schemeId).append(
                        $('<option>', {
                            value: scheme.id,
                            text: scheme.name
                        })
                    );

                });

                // BATCHES
                $(section.batchId).html(
                    '<option value="">--Select Batch--</option>'
                );

                $.each(response.batches || [], function (_, batch) {

                    $(section.batchId).append(
                        $('<option>', {
                            value: batch.id,
                            text: batch.name
                        })
                    );

                });

                let courseType = $(section.courseType).val();

                // SEMESTER-BASED COURSE
                if (courseType === 'Semester Based') {

                    $(section.semesterContainer).show();

                    $(section.semesterId).html(
                        '<option value="">--Select Semester--</option>'
                    );

                    $.each(
                        response.semesters || [],
                        function (_, semester) {

                            $(section.semesterId).append(
                                $('<option>', {
                                    value: semester.id,
                                    text: semester.name
                                })
                            );

                        }
                    );
                }

                // ANNUAL-BASED COURSE
                if (courseType === 'Annual Based') {

                    $(section.annualSemesterContainer).show();

                    $(section.annualSemesterId).html(
                        '<option value="">--Select Annual Terms--</option>'
                    );

                    $.each(
                        response.annual_semesters || [],
                        function (_, annual) {

                            $(section.annualSemesterId).append(
                                $('<option>', {
                                    value: annual.id,
                                    text: annual.name
                                })
                            );

                        }
                    );
                }

                // Refresh subjects after course data loads.
                loadSubjects(section);

            }
        ).fail(function () {

            $(section.schemeId).html(
                '<option value="">Failed to load schemes</option>'
            );

            $(section.batchId).html(
                '<option value="">Failed to load batches</option>'
            );

        });
    }

    // ==========================================
    // INITIALIZE BOTH SECTIONS
    // ==========================================
    $.each(sections, function (_, section) {

        // COURSE CHANGE
        $(section.courseId).on('change', function () {

            loadCourseData(section, $(this).val());

        });

        // COURSE TYPE CHANGE
        $(section.courseType).on('change', function () {

            resetSemesterUI(section);

            let courseId = $(section.courseId).val();

            if (courseId) {
                loadCourseData(section, courseId);
            }

        });

        // FILTER CHANGE
        $(
            section.branchId + ', ' +
            section.schemeId + ', ' +
            section.semesterId + ', ' +
            section.annualSemesterId
        ).on('change', function () {

            loadSubjects(section);

        });

        // LOAD EXISTING SELECTED COURSE
        let defaultCourse = $(section.courseId).val();
        let defaultType = $(section.courseType).val();

        if (defaultCourse && defaultType) {
            loadCourseData(section, defaultCourse);
        }

    });

});
</script>

<script>
    // =========================
    // FILTER
    // =========================
    $('#applyFilter').on('click', function () {

        // if (!$('#roll_no').val()) return alert('Roll No Required');
        if (!$('#course_type').val()) return alert('Course Type Required');
        if (!$('#course_id').val()) return alert('Course Required');
        if (!$('#branch_id').val()) return alert('Branch Required');
        if (!$('#batch_id').val()) return alert('Batch Required');

        if ($('#course_type').val() === 'Semester Based' && !$('#semester_id').val()) {
            return alert('Semester Required');
        }

        if ($('#course_type').val() === 'Annual Based' && !$('#annual_semester_id').val()) {
            return alert('Annual Semester Required');
        }

        loadStudentSubjects();
    });


    // =========================
    // LOAD DATA
    // =========================
    function loadStudentSubjects() {

        $.ajax({
            url: "{{ route('student-subject.render') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",

                roll_no: $('#roll_no').val(),
                course_type: $('#course_type').val(),
                course_id: $('#course_id').val(),
                branch_id: $('#branch_id').val(),
                scheme_id: $('#scheme_id').val(),
                semester_id: $('#semester_id').val(),
                annual_semester_id: $('#annual_semester_id').val(),
                batch_id: $('#batch_id').val(),
            },

            beforeSend: function () {
                $('.student-data').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p>Loading...</p>
                    </div>
                `);
            },

            success: function (res) {

                if (!res.status) {
                    $('.student-data').html(`
                        <div class="alert alert-danger">${res.message}</div>
                    `);
                    return;
                }

                $('.student-data').html(res.html);

                $('#offcanvasTop').offcanvas('hide');
            }
        });
    }


    // =========================
    // SELECT ALL
    // =========================
    
    // $(document).on('change', '#select_all', function () {
    //     $('.subject-checkbox').prop('checked', $(this).is(':checked'));
    // });
    
    $(document).on('change', '.select-all-student-subjects', function () {
    
        let card = $(this).closest('.student-subject-card');
    
        card.find('.subject-checkbox').prop(
            'checked',
            $(this).is(':checked')
        );
    
    });

</script>


<script>
    // =========================
    // SAVE SUBJECT SELECTION
    // =========================
    
    $(document).on('click', '#saveSelectionSubject', function () {

        let btn = $(this);
        let originalText = btn.html();
    
        let studentSubjects = {};
    
        // Collect every student's selected subjects separately
        $('.student-subject-card').each(function () {
    
            let studentId = $(this).data('student');
    
            let selectedSubjects = [];
    
            $(this).find('.subject-checkbox:checked').each(function () {
                selectedSubjects.push($(this).val());
            });
    
            studentSubjects[studentId] = selectedSubjects;
    
        });
    
        $.ajax({
    
            url: "{{ route('student-subject.store') }}",
    
            type: "POST",
    
            data: {
    
                _token: "{{ csrf_token() }}",
    
                roll_no: $('#roll_no').val(),
    
                course_type: $('#course_type').val(),
    
                course_id: $('#course_id').val(),
    
                branch_id: $('#branch_id').val(),
    
                scheme_id: $('#scheme_id').val(),
    
                semester_id: $('#semester_id').val(),
    
                annual_semester_id: $('#annual_semester_id').val(),
    
                batch_id: $('#batch_id').val(),
    
                student_subjects: studentSubjects
    
            },
    
            beforeSend: function () {
    
                btn.prop('disabled', true);
    
                btn.html('Please wait...');
    
            },
    
            success: function (res) {
    
                btn.prop('disabled', false);
    
                btn.html(originalText);
    
                if (!res.status) {
    
                    alert(res.message);
    
                    return;
                }
    
                Toastify({
    
                    text: res.message,
    
                    duration: 2000,
    
                    gravity: "top",
    
                    position: "right",
    
                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
    
                }).showToast();
    
                loadStudentSubjects();
    
            },
    
            error: function (xhr) {
    
                btn.prop('disabled', false);
    
                btn.html(originalText);
    
                Toastify({
    
                    text: xhr.responseJSON?.message || 'Something went wrong',
    
                    duration: 3000,
    
                    gravity: "top",
    
                    position: "right"
    
                }).showToast();
    
            }
    
        });
    
    });
    
    
    // $(document).on('click', '#saveSelectionSubject', function () {

    //     let btn = $(this);
    //     let originalText = btn.html();

    //     let selectedSubjects = [];

    //     $('.subject-checkbox:checked').each(function () {
    //         selectedSubjects.push($(this).val());
    //     });

    //     $.ajax({
    //         url: "{{ route('student-subject.store') }}",
    //         type: "POST",
    //         data: {
    //             _token: "{{ csrf_token() }}",

    //             roll_no: $('#roll_no').val(),
    //             course_type: $('#course_type').val(),
    //             course_id: $('#course_id').val(),
    //             branch_id: $('#branch_id').val(),
    //             scheme_id: $('#scheme_id').val(),
    //             semester_id: $('#semester_id').val(),
    //             annual_semester_id: $('#annual_semester_id').val(),
    //             batch_id: $('#batch_id').val(),

    //             subjects: selectedSubjects
    //         },

            
    //         beforeSend: function () {
    //             btn.prop('disabled', true);
    //             btn.html('Please wait...');
    //         },

    //         success: function (res) {

                
    //             btn.prop('disabled', false);
    //             btn.html(originalText);

    //             if (!res.status) {
    //                 alert(res.message);
    //                 return;
    //             }

    //             Toastify({
    //                 text: res.message,
    //                 duration: 2000,
    //                 gravity: "top",
    //                 position: "right",
    //                 backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
    //             }).showToast();

                
    //             loadStudentSubjects();
    //         },

    //         error: function () {

    //             btn.prop('disabled', false);
    //             btn.html(originalText);

    //             alert('Something went wrong');
    //         }
    //     });
    // });
</script>

<script>
let table;

$(function () {

    table = $('.yajra-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('student-subject.list') }}",
            data: function (d) {

                d.roll_no = $('#roll_no').val();
                d.course_type = $('#course_type').val();
                d.course_id = $('#course_id').val();
                d.branch_id = $('#branch_id').val();
                d.scheme_id = $('#scheme_id').val();
                d.semester_id = $('#semester_id').val();
                d.annual_semester_id = $('#annual_semester_id').val();
                d.batch_id = $('#batch_id').val();
            }
        },

        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },

            { data: 'student_name' },
            { data: 'university_roll_no' },
            { data: 'father_name' },
            { data: 'course_name' },
            { data: 'branch_name' },
            { data: 'semester_name' },

            {
                data: 'subjects',
                orderable: false,
                searchable: false,
                render: function (data) {

                    if (!data) return '<span class="text-muted">N/A</span>';

                    let subjects = data.split('||');

                    let html = '<ul class="mb-0 ps-3">';

                    subjects.forEach(sub => {
                        html += `<li>${sub.trim()}</li>`;
                    });

                    html += '</ul>';

                    return html;
                }
            }
        ]
    });

});
</script>

<script>
$('#applyStudentFilter').on('click', function () {

    if (!$('#course_type').val()) return alert('Course Type Required');
    if (!$('#course_id').val()) return alert('Course Required');
    if (!$('#branch_id').val()) return alert('Branch Required');
    if (!$('#batch_id').val()) return alert('Batch Required');

    if ($('#course_type').val() === 'Semester Based' && !$('#semester_id').val()) {
        return alert('Semester Required');
    }

    if ($('#course_type').val() === 'Annual Based' && !$('#annual_semester_id').val()) {
        return alert('Annual Semester Required');
    }

    // Reload DataTable with filters
    table.ajax.reload();

    // Close offcanvas
    $('#filterOffcanvasTop').offcanvas('hide');
});
</script>



<script>
    $('#exportSampleButton').on('click', function () {
    
        let rollNo = $('#export_roll_no').val().trim();
        let courseType = $('#export_course_type').val();
        let courseId = $('#export_course_id').val();
        let schemeId = $('#export_scheme_id').val();
        let branchId = $('#export_branch_id').val();
        let semesterId = $('#export_semester_id').val();
        let annualSemesterId = $('#export_annual_semester_id').val();
        let batchId = $('#export_batch_id').val();
    
        if (!courseType || !courseId || !branchId || !batchId) {
            Toastify({
                text: 'Please select Course Type, Course, Branch and Batch.',
                duration: 3000
            }).showToast();
            return;
        }
    
        if (courseType === 'Semester Based' && !semesterId) {
            Toastify({
                text: 'Please select Semester.',
                duration: 3000
            }).showToast();
            return;
        }
    
        if (courseType === 'Annual Based' && !annualSemesterId) {
            Toastify({
                text: 'Please select Annual Year.',
                duration: 3000
            }).showToast();
            return;
        }
    
        let params = new URLSearchParams();
    
        params.append('roll_no', rollNo);
        params.append('course_type', courseType);
        params.append('course_id', courseId);
        params.append('scheme_id', schemeId);
        params.append('branch_id', branchId);
        params.append('semester_id', semesterId);
        params.append('annual_semester_id', annualSemesterId);
        params.append('batch_id', batchId);
    
        window.location.href =
            "{{ route('student-subject.export-sample') }}?" + params.toString();
    });
</script>