@extends('institute.partial.master')
@section('title', 'Students')
@section('sub_title', 'Manage Students')
@section('institute-content')
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

                    <a href="{{ route('institute.students.create') }}" class="btn btn-sm btn-primary">
                        <i data-feather="plus-circle" style="width:14px; height:14px;" class="me-1"></i>
                        Create New
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive database-table">
                    <table class="table table-bordered yajra-datatable w-100">
                        <thead class="table-primary">
                            <tr>
                                <th><input type="checkbox" name="select_all" id="select_all"> #</th>
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
            <div class="card-footer">
                <button id="promote_students" class="btn btn-primary mt-3">
                    Promote Selected Students
                </button>
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
            <form action="{{ route('institute.students.import') }}" method="post" enctype="multipart/form-data">
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
    $(function () {

        $('.yajra-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('institute.students.data') }}",

            columns: [
                { data: 'checkbox', orderable: false, searchable: false },
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

    });

    // promot student ---------------------
    // Select all
    $(document).on('click', '#select_all', function () {
        $('.student_checkbox').prop('checked', this.checked);
    });

    // Individual uncheck sync
    $(document).on('change', '.student_checkbox', function () {
        if (!this.checked) {
            $('#select_all').prop('checked', false);
        }
    });


    $('#promote_students').click(function () {

        let btn = $(this);
        let ids = [];

        $('.student_checkbox:checked').each(function () {
            ids.push($(this).val());
        });

        if (ids.length === 0) {
            Toastify({
                text: "Please select at least one student",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#dc3545",
            }).showToast();
            return;
        }

        // Confirm Dialog
        if (!confirm('Are you sure you want to move selected students to next semester/year?')) {
            return;
        }

        $.ajax({
            url: "{{ route('institute.students.promote') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                ids: ids
            },

            // Before Send (Button Disable + Loader Text)
            beforeSend: function () {
                btn.prop('disabled', true);
                btn.data('original-text', btn.html());
                btn.html('Please wait...');
            },

            success: function (res) {

                Toastify({
                    text: res.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745",
                }).showToast();

                $('.yajra-datatable').DataTable().ajax.reload();
            },

            error: function () {
                Toastify({
                    text: "Something went wrong!",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#dc3545",
                }).showToast();
            },

            // ✅ Always (restore button)
            complete: function () {
                btn.prop('disabled', false);
                btn.html(btn.data('original-text'));
            }
        });

    });

    // $('#promote_students').click(function () {
        
    //     let ids = [];

    //     $('.student_checkbox:checked').each(function () {
    //         ids.push($(this).val());
    //     });

    //     if (ids.length === 0) {
    //         Toastify({
    //             text: "Please select at least one student",
    //             duration: 3000,
    //             gravity: "top",
    //             position: "right",
    //             backgroundColor: "#dc3545", // red
    //         }).showToast();
    //         return;
    //     }

    //     $.ajax({
    //         url: "{{ route('institute.students.promote') }}",
    //         type: "POST",
    //         data: {
    //             _token: "{{ csrf_token() }}",
    //             ids: ids
    //         },
    //         success: function (res) {

    //             Toastify({
    //                 text: res.message,
    //                 duration: 3000,
    //                 gravity: "top",
    //                 position: "right",
    //                 backgroundColor: "#28a745", // green
    //             }).showToast();

    //             $('.yajra-datatable').DataTable().ajax.reload();
    //         },

    //         error: function () {
    //             Toastify({
    //                 text: "Something went wrong!",
    //                 duration: 3000,
    //                 gravity: "top",
    //                 position: "right",
    //                 backgroundColor: "#dc3545",
    //             }).showToast();
    //         }
    //     });

    // });

</script>

@endsection