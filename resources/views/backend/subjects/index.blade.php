@extends('backend.partial.master')
@section('title', 'Subject')
@section('sub_title', 'Subject List')
@section('backend-content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="card-title m-0">Subject List</h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#importSubjectModal">
                        <i data-feather="upload" style="width:14px; height:14px;" class="me-1"></i>
                        Import Subject
                    </button>

                    <a href="{{ route('subjects.create') }}" class="btn btn-sm btn-primary">
                        <i data-feather="plus-circle" style="width:14px; height:14px;" class="me-1"></i>
                        Create New
                    </a>

                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="offcanvas" data-bs-target="#offcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i>
                    </button>

                    <div class="offcanvas offcanvas-top" tabindex="-1" id="offcanvasTop" aria-labelledby="offcanvasTopLabel">
                        <div class="offcanvas-header">
                            <h5 id="offcanvasTopLabel">Filter Subject</h5>
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body card">
                            <div class="row">
                                <div class="col-md-2 form-group mb-2">
                                    <label for="course_type">Course Type<span class="text-danger">*</span></label>
                                    <select name="course_type" id="course_type" class="form-control">
                                        <option value="">--Select--</option>
                                        <option value="Annual Based">Annual Based</option>
                                        <option value="Semester Based">Semester Based</option>
                                    </select>
                                    @error('course_type') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-2 form-group mb-2">
                                    <label for="course_id">Select Course<span class="text-danger">*</span></label>
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
                                    @error('scheme_id') <small class="text-danger">{{ $message }}</small> @enderror
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
                                <div class="col-md-2 form-group mt-3 mb-2">
                                    <button type="button" id="applyFilter" class="btn btn-primary">Filter</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive database-table">
                    <table class="table table-bordered table-bordered yajra-datatable">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Credit</th>
                                <th>Course</th>
                                <th>Scheme</th>
                                <th>Branch</th>
                                <th>Semester</th>
                                <th>Int. Marks</th>
                                <th>Ext. Marks</th>
                                <th>Is NC</th>
                                <th scope="col" id="no-export">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="importSubjectModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="importSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="importSubjectModalLabel">Import Subject</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('subjects.import') }}" method="post" enctype="multipart/form-data">
                @csrf
            <div class="modal-body">
                <div class="alert alert-info" role="alert">
                    <h5 class="alert-heading"><i class="mdi mdi-information-outline me-2"></i>Subject CSV Import Instructions</h5>
                    <ol class="mb-0">
                        <li><strong>Upload only CSV files.</strong> Other file types will not be accepted.</li>
                        <li><strong>Do not change the column headers.</strong> The first row must exactly match:
                            <code>course_type, course_id, branch_id, scheme_id, semester_id, name, type, subject_code, credits, internal_marks, external_marks, total</code>.
                        </li>
                        <li><strong>Before uploading, confirm:</strong> the selected course type, course, scheme, branch, and semester match your CSV data.</li>
                        <li><strong>Enter only integer values</strong> in the columns: credits, internal_marks, external_marks, and total.</li>
                        <li><strong>Download the sample CSV</strong> template before uploading to ensure proper formatting.</li>
                        <li><strong>Avoid extra spaces</strong> or special characters in the sheet to prevent import errors.</li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Download Subject Sample</h5>
                                <a href="{{ asset('assets/sample/subjects.csv') }}"
                                class="btn btn-primary"
                                download="subjects_sample.csv">
                                    <i class="mdi mdi-download me-1"></i> Download
                                </a>
                            </div>
                            {{-- <div class="card-body d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Download Annual Terms Sample</h5>
                                <a href="{{ asset('assets/sample/annualsubjects.csv') }}"
                                class="btn btn-primary"
                                download="annualsubjects_sample.csv">
                                    <i class="mdi mdi-download me-1"></i> Download
                                </a>
                            </div> --}}
                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group">
                                    {{-- <label for="">Import Subject</label> --}}
                                    <input type="file" name="file" class="form-control" accept=".csv" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Import Student</button>
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

        $('#branch_id').html('<option>Please wait...</option>');
        $('#scheme_id').html('<option>Please wait...</option>');

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

            let courseType = $('#course_type').val();

            /* ---------- Semester Based ---------- */
            if (courseType === 'Semester Based') {
                $('#semester_container').show();
                $('#semester_id').html('<option value="">--Select Semester--</option>');

                $.each(response.semesters, function (_, semester) {
                    $('#semester_id').append(
                        `<option value="${semester.id}">${semester.name}</option>`
                    );
                });
            }

            /* ---------- Annual Based ---------- */
            if (courseType === 'Annual Based') {
                $('#annual_semester_container').show();
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
       AUTO LOAD ON PAGE LOAD
       (EDIT / DEFAULT VALUES)
    ==========================*/
    let defaultCourse = $('#course_id').val();
    let defaultType   = $('#course_type').val();

    if (defaultCourse && defaultType) {
        loadCourseData(defaultCourse);
    }

});
</script>


<script>
    // $(function () {

    //     $('.yajra-datatable').DataTable({
    //         processing: true,
    //         serverSide: true,
    //         ajax: "{{ route('subjects.data') }}",

    //         columns: [
    //             { data: 'DT_RowIndex', orderable: false, searchable: false },
    //             {
    //                 data: 'name',
    //                 render: function (data, type, row) {
    //                     return `
    //                         <strong>${row.name}</strong><br>
    //                         <small class="text-primary"><b>Code: ${row.subject_code ?? '-'}</b></small>
    //                     `;
    //                 }
    //             },
    //             { data: 'type' },
    //             { data: 'credits' },
    //             { data: 'course' },
    //             { data: 'scheme' },
    //             { data: 'branch' },
    //             { data: 'semester' },
    //             { data: 'internal_marks' },
    //             { data: 'external_marks' },
    //             { data: 'action', orderable: false, searchable: false }
    //         ],

    //         order: [[4, 'asc'], [6, 'asc'], [5, 'asc'], [7, 'asc']],

    //         lengthMenu: AppDataTable.lengthMenu,
    //         pageLength: AppDataTable.pageLength,
    //         dom: AppDataTable.dom,
    //         buttons: AppDataTable.buttons,
    //         drawCallback: AppDataTable.drawCallback
    //     });

    // });


</script>

<script>
let subjectTable;

$(function () {

    subjectTable = $('.yajra-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('subjects.data') }}",
            data: function (d) {
                d.course_type        = $('#course_type').val();
                d.course_id          = $('#course_id').val();
                d.scheme_id          = $('#scheme_id').val();
                d.branch_id          = $('#branch_id').val();
                d.semester_id        = $('#semester_id').val();
                d.annual_semester_id = $('#annual_semester_id').val();
            },
            beforeSend: function () {
                $('#applyFilter')
                    .prop('disabled', true)
                    .html('Please wait...');
            },
            complete: function () {
                $('#applyFilter')
                    .prop('disabled', false)
                    .html('Filter');

                // 🔥 Offcanvas close after data loaded
                let offcanvasEl = document.getElementById('offcanvasTop');
                let offcanvas   = bootstrap.Offcanvas.getInstance(offcanvasEl);

                if (offcanvas) {
                    offcanvas.hide();
                }
            }
        },

        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            {
                data: 'name',
                render: function (data, type, row) {
                    return `
                        <strong>${row.name}</strong><br>
                        <small class="text-primary"><b>Code: ${row.subject_code ?? '-'}</b></small>
                    `;
                }
            },
            { data: 'type' },
            { data: 'credits' },
            { data: 'course' },
            { data: 'scheme' },
            { data: 'branch' },
            { data: 'semester' },
            { data: 'internal_marks' },
            { data: 'external_marks' },
            { data: 'is_nc', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false }
        ],

        order: [[4, 'asc'], [6, 'asc'], [5, 'asc'], [7, 'asc']],

        lengthMenu: AppDataTable.lengthMenu,
        pageLength: AppDataTable.pageLength,
        dom: AppDataTable.dom,
        buttons: AppDataTable.buttons,
        drawCallback: AppDataTable.drawCallback
    });

});

/* =========================
   APPLY FILTER BUTTON
=========================*/
$('#applyFilter').on('click', function () {

    let courseType = $('#course_type').val();
    let courseId   = $('#course_id').val();

    if (!courseType || !courseId) {
        alert('Course Type & Course are mandatory!');
        return;
    }

    subjectTable.ajax.reload();
});
</script>



@endsection
