@extends('backend.partial.master')
@section('title', 'Student')
@section('sub_title', 'Student List')
@section('backend-content')
<style>
#studentOffcanvasTop .offcanvas-body {
    min-height: 200px;
}
</style>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title m-0">Student List</h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#importStudentModal">
                        <i data-feather="upload" style="width:14px; height:14px;" class="me-1"></i>
                        Import Student
                    </button>

                    <a href="{{ route('students.create') }}" class="btn btn-sm btn-primary">
                        <i data-feather="plus-circle" style="width:14px; height:14px;" class="me-1"></i>
                        Create New
                    </a>

                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="offcanvas" data-bs-target="#studentOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i>
                    </button>


                    <div class="offcanvas offcanvas-top" tabindex="-1" id="studentOffcanvasTop" aria-labelledby="studentOffcanvasTopLabel">
                        <div class="offcanvas-header">
                            <h5 id="studentOffcanvasTopLabel">Search Student</h5>
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body card">
                            <form id="studentSearchForm">
                                <div class="row">
                                    <div class="col-md-2 form-group mb-2">
                                        <label for="student_roll_number">Student Roll Number</label>
                                        <input type="text" name="student_roll_number" id="student_roll_number" class="form-control">
                                    </div>
                                    <div class="col-md-2 form-group mb-2 d-none">
                                        <label for="course_type">Course Type</label>
                                        <select name="course_type" id="course_type" class="form-control">
                                            <option value="">--Select--</option>
                                            {{-- <option value="Annual Based">Annual Based</option> --}}
                                            <option value="Semester Based" selected>Semester Based</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group mb-2">
                                        <label for="course_id">Select Course</label>
                                        <select name="course_id" id="course_id" class="form-control">
                                            <option value="">--Select--</option>
                                        @foreach ($courses as $course)
                                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group mb-2">
                                        <label for="scheme_id">Select Scheme</label>
                                        <select name="scheme_id" id="scheme_id" class="form-control">
                                            <option value="">--Select--</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group mb-2">
                                        <label for="branch_id">Select Branch</label>
                                        <select name="branch_id" id="branch_id" class="form-control">
                                            <option value="">--Select--</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group mb-2" id="semester_container"  style="display: none;">
                                        <label for="semester_id">Select Semester</label>
                                        <select name="semester_id" id="semester_id" class="form-control">
                                            <option value="">--Select--</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group mb-2" id="annual_semester_container"  style="display: none;">
                                        <label for="annual_semester_id">Select Annual Terms</label>
                                        <select name="annual_semester_id" id="annual_semester_id" class="form-control">
                                            <option value="">--Select--</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group mb-2">
                                        <label>Session / Batch </label>
                                        <select name="batch_id" id="batch_id" class="form-control">
                                            <option value="">--Select--</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group mb-2" id="subject_container">
                                        <label for="institute_id">Select Institute</label>
                                        <select name="institute_id" id="institute_id" class="form-control">
                                            <option value="">--Select--</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group mt-3 mb-2">
                                        <button type="button" id="searchStudent" class="btn btn-primary">Filter Student</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>


                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive database-table">
                    <table class="table table-bordered yajra-datatable w-100">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Father</th>
                                <th>Uni. Roll</th>
                                <th>Course</th>
                                <th>Branch</th>
                                <th>Semester</th>
                                <th>Scheme</th>
                                <th>Batch</th>
                                <th>Institute</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>

                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="importStudentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="importStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="importStudentModalLabel">Import Student</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('students.import') }}" method="post" enctype="multipart/form-data">
                @csrf
            <div class="modal-body">
                <div class="alert alert-info" role="alert">
                    <h5 class="alert-heading">
                        <i class="mdi mdi-information-outline me-2"></i>
                        Student CSV Import Instructions
                    </h5>

                    <ul class="mb-0">
                        <li><strong>Upload CSV files only.</strong> Other file formats are not supported.</li>

                        <li>
                            <strong>Do not modify the column headers.</strong>
                            The first row of the CSV file must exactly match the following order:
                            <br>
                            <code>
                                university_roll_no, reg_no, student_name, father_name, mother_name,
                                abcid, gender, dob, aadhar, current_address, parmanent_address,
                                phone, email, nationality, state, category, institute_id,
                                profile_image, signature, password, remember_token,
                                course_id, branch_id, semester_id/annual_semester_id, scheme_id, batch_id,
                                pursuing_status
                            </code>
                        </li>

                        <li>
                            <strong>Mandatory fields:</strong>
                            <code>university_roll_no, student_name, gender, dob, phone,
                            institute_id, course_id, branch_id, semester_id/annual_semester_id, scheme_id, batch_id</code>
                        </li>

                        <li>
                            <strong>Unique values:</strong>
                            <code>university_roll_no</code> must be unique for each student.
                            Duplicate values will be rejected.
                        </li>

                        <li>
                            <strong>Gender values:</strong> Use only
                            <code>male</code> or <code>female</code>.
                        </li>

                        <li>
                            <strong>Date format:</strong>
                            <code>dob</code> must be in <code>YYYY-MM-DD</code> format.
                        </li>

                        <li>
                            <strong>Numeric fields:</strong>
                            Use numbers only for
                            <code>aadhar, phone</code>.
                        </li>

                        <li>
                            <strong>Images:</strong>
                            Leave <code>profile_image</code> and <code>signature</code> blank during CSV import.
                            Images can be uploaded later from the student profile.
                        </li>

                        <li>
                            <strong>Password:</strong>
                            Provide a valid password. It will be automatically encrypted after import.
                        </li>

                        <li>
                            <strong>Pursuing status:</strong>
                            Allowed values:
                            <code>yes</code> or <code>no</code>.
                        </li>

                        <li>
                            <strong>System fields:</strong>
                            <code>created_at</code> and <code>updated_at</code> will be auto-generated.
                            Do not include them in the CSV file.
                        </li>

                        <li>
                            Avoid extra spaces, empty rows, formulas, or special characters
                            to prevent import errors.
                        </li>
                    </ul>
                </div>


                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Download Sample CSV</h5>
                                <a href="{{ asset('assets/sample/students.csv') }}"
                                class="btn btn-primary"
                                download="student_sample.csv">
                                    <i class="mdi mdi-download me-1"></i> Download
                                </a>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group">
                                    <input type="file" name="file" class="form-control" accept=".csv" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Import Subject</button>
            </div>
            </form>
        </div>
    </div>
</div>


<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>


<script>
$(document).ready(function () {

    function resetSemesterUI() {
        $('#semester_container').hide();
        $('#annual_semester_container').hide();

        $('#semester_id').html('<option value="">--Select Semester--</option>');
        $('#annual_semester_id').html('<option value="">--Select--</option>');
    }

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
<script>
    var table;

    $(document).ready(function () {

        table = $('.yajra-datatable').DataTable({

            processing: true,
            serverSide: true,
            destroy: true,

            ajax: {
                url: "{{ route('students.data') }}",
                type: "GET",
                data: function (d) {

                    d.student_roll_number = $('#student_roll_number').val();
                    d.course_type         = $('#course_type').val();
                    d.course_id           = $('#course_id').val();
                    d.branch_id           = $('#branch_id').val();
                    d.scheme_id           = $('#scheme_id').val();
                    d.semester_id         = $('#semester_id').val();
                    d.annual_semester_id  = $('#annual_semester_id').val();
                    d.batch_id            = $('#batch_id').val();
                    d.institute_id        = $('#institute_id').val();
                }
            },

            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'student', name: 'student_name' },
                { data: 'father', name: 'father_name' },
                { data: 'university_roll', name: 'university_roll_no' },
                { data: 'course', name: 'course.name' },
                { data: 'branch', name: 'branch.name' },
                { data: 'semester', name: 'semester.name' },
                { data: 'scheme', name: 'scheme.name' },
                { data: 'batch', name: 'batch.name' },
                { data: 'institute', name: 'institute.name' },
                { data: 'phone', name: 'phone' },
                { data: 'status', orderable: false, searchable: false },
                { data: 'action', orderable: false, searchable: false }
            ],

            order: [[1, 'asc']],

            lengthMenu: AppDataTable.lengthMenu,
            pageLength: AppDataTable.pageLength,
            dom: AppDataTable.dom,
            buttons: AppDataTable.buttons,
            drawCallback: AppDataTable.drawCallback
        });

        // ==========================
        // Filter Button
        // ==========================
        $('#searchStudent').on('click', function (e) {

            e.preventDefault();

            var btn = $(this);

            btn.prop('disabled', true)
               .html('<span class="spinner-border spinner-border-sm me-1"></span> Please Wait...');

            table.ajax.reload();
        });

        // ==========================
        // Data Loaded
        // ==========================
        table.on('draw', function () {

            // Enable Button
            $('#searchStudent')
                .prop('disabled', false)
                .html('Filter Student');

            // Close Offcanvas
            let offcanvasElement = document.getElementById('studentOffcanvasTop');
            let offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);

            if (offcanvas) {
                offcanvas.hide();
            }

        });

    });

</script>


@endsection
