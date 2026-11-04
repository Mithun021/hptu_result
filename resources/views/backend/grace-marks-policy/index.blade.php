@extends('backend.partial.master')
@section('title', 'Grace Marks Policy')
@section('sub_title', 'Grace Marks')
@section('backend-content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title m-0">Grace Mark Policy</h4></div>
            <form action="{{ route('grace-mark-policies.store') }}" method="post"> @csrf
                <div class="card-body">
                    <div class="form-group mb-2">
                        <label for="">Course Type</label>
                        <select name="course_type" id="course_type" class="form-control">
                            <option value="">-select--</option>
                            <option value="Semester Based" >Semester Based</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label for="course_id">Select Course<span class="text-danger">*</span></label>
                        <select name="course_id" id="course_id" class="form-control">
                            <option value="">--Select--</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label for="scheme_id">Select Scheme</label>
                        <select name="scheme_id" id="scheme_id" class="form-control">
                            <option value="">--Select--</option>
                        </select>
                        @error('scheme_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group mb-2">
                        <label for="branch_id">Select Branch<span class="text-danger">*</span></label>
                        <select name="branch_id" id="branch_id" class="form-control">
                            <option value="">--Select--</option>
                        </select>
                    </div>
                    <div class="form-group mb-2" id="semester_container"  style="display: none;">
                        <label for="semester_id">Select Semester</label>
                        <select name="semester_id" id="semester_id" class="form-control">
                            <option value="">--Select--</option>
                        </select>
                    </div>
                    <div class="form-group mb-2" id="annual_semester_container"  style="display: none;">
                        <label for="annual_semester_id">Select Annual Terms</label>
                        <select name="annual_semester_id" id="annual_semester_id" class="form-control">
                            <option value="">--Select--</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label>Session / Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" id="batch_id" class="form-control">
                            <option value="">--Select--</option>
                        </select>
                    </div>
                    <div class="form-group m-2">
                        <label for="">Academic Year</label>
                        <input type="text" class="form-control" name="academic_year">
                    </div>
                    <div class="form-group m-2">
                        <label for="">Max Grace Marks</label>
                        <input type="text" class="form-control" name="max_grace_marks">
                    </div>
                    <div class="form-group mb-2">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title m-0">Grace Mark Policy List</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive database-table">
                    <table class="table table-bordered table-bordered"  id="responsive-datatable">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Course Type</th>
                                <th>Course Name</th>
                                <th>Branch Name</th>
                                <th>Batch</th>
                                <th>Academic Year</th>
                                <th>Maximum Marks</th>
                                <th scope="col" id="no-export">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($policies as $index => $item)
                                <tr>
                                    <td>{{ ++$index }}</td>
                                    <td>{{ $item->course_type  }}</td>
                                    <td>{{ $item->course->name  }}</td>
                                    <td>{{ $item->branch->name  }}</td>
                                    <td>{{ $item->batch->name  }}</td>
                                    <td>{{ $item->academic_year  }}</td>
                                    <td>{{ $item->max_grace_marks  }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-transparent dropdown-toggle" type="button" 
                                                    id="actionDropdown{{ $item->id }}" data-bs-toggle="dropdown" 
                                                    aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical fs-16"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="actionDropdown{{ $item->id }}">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('grace-mark-policies.edit', $item->id) }}">
                                                        <i class="mdi mdi-pencil-outline me-2"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('grace-mark-policies.destroy', $item->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item" onclick="return confirm('Delete this grace marks policy?')">
                                                            <i class="mdi mdi-delete-outline me-2"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script> 
<script>
$(document).ready(function () {

    // function resetSemesterUI() {
    //     $('#semester_container').hide();
    //     $('#annual_semester_container').hide();
    //     $('#semester_id').html('<option value="">--Select Semester--</option>');
    //     $('#annual_semester_id').html('<option value="">--Select--</option>');
    // }

    function loadCourseData(courseId) {
        if (!courseId) return;

        // resetSemesterUI();

        $('#branch_id').html('<option>Please wait...</option>');
        $('#scheme_id').html('<option>Please wait...</option>');
        $('#batch_id').html('<option>Please wait...</option>');

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

            let courseType = $('#course_type').val();

            /* ---------- Semester Based ---------- */
            // if (courseType === 'Semester Based') {
            //     $('#semester_container').show();
            //     $('#semester_id').html('<option value="">--Select Semester--</option>');

            //     $.each(response.semesters, function (_, semester) {
            //         $('#semester_id').append(
            //             `<option value="${semester.id}">${semester.name}</option>`
            //         );
            //     });
            // }

            /* ---------- Annual Based ---------- */
            // if (courseType === 'Annual Based') {
            //     $('#annual_semester_container').show();
            //     $('#annual_semester_id').html('<option value="">--Select--</option>');

            //     $.each(response.annual_semesters, function (_, annual) {
            //         $('#annual_semester_id').append(
            //             `<option value="${annual.id}">${annual.name}</option>`
            //         );
            //     });
            // }
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
        // resetSemesterUI();
        let courseId = $('#course_id').val();
        if (courseId) {
            loadCourseData(courseId);
        }
    });
});

</script>

@endsection