@extends('backend.partial.master')

@section('title', 'Block Student')
@section('sub_title', 'Block Student')

@section('backend-content')

<div class="row">

    {{-- BLOCK STUDENT FORM --}}
    <div class="col-12">
        <div class="card">

            <div class="card-header">
                <h4 class="card-title mb-0">Block Student</h4>
            </div>

            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('block-students.store') }}"
                      method="POST">

                    @csrf

                    {{-- FIRST ROW --}}
                    <div class="row">

                        {{-- EXAM --}}
                        <div class="col-md-6 mb-3">

                            <label for="exam_id" class="form-label">
                                Select Examination <span class="text-danger">*</span>
                            </label>

                            <select name="exam_id"
                                    id="exam_id"
                                    class="form-select"
                                    required>

                                <option value="">Select Examination</option>

                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}"
                                        {{ old('exam_id') == $exam->id ? 'selected' : '' }}>
                                        {{ $exam->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        {{-- HOLD TYPE --}}
                        <div class="col-md-6 mb-3">

                            <label for="hold_type" class="form-label">
                                Hold Type <span class="text-danger">*</span>
                            </label>

                            <select name="hold_type"
                                    id="hold_type"
                                    class="form-select"
                                    required>

                                <option value="">Select Hold Type</option>

                                <option value="overall"
                                    {{ old('hold_type') == 'overall' ? 'selected' : '' }}>
                                    Overall
                                </option>

                                <option value="sem-wise"
                                    {{ old('hold_type') == 'sem-wise' ? 'selected' : '' }}>
                                    Sem-Wise
                                </option>

                            </select>

                        </div>

                    </div>


                    {{-- SECOND ROW --}}
                    <div class="row">

                        {{-- STUDENT --}}
                        <div class="col-md-6 mb-3">

                            <label for="university_roll_no" class="form-label">
                                University Roll Number <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                name="university_roll_no"
                                id="university_roll_no"
                                class="form-control"
                                value="{{ old('university_roll_no') }}"
                                placeholder="Enter University Roll Number"
                                required>

                        </div>

                        {{-- SEMESTER --}}
                        <div class="col-md-6 mb-3"
                             id="semesterWrapper"
                             style="display: none;">

                            <label for="semester_id" class="form-label">
                                Select Semester <span class="text-danger">*</span>
                            </label>

                            <select name="semester_id"
                                    id="semester_id"
                                    class="form-select">

                                <option value="">Select Semester</option>

                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}"
                                        {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
                                        {{ $semester->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                    </div>


                    {{-- SUBMIT --}}
                    <div class="row">
                        <div class="col-12 text-end">

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i>
                                Block Student
                            </button>

                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>


    {{-- BLOCK STUDENT DATA TABLE --}}
    <div class="col-12">

        <div class="card">

            <div class="card-header">
                <h4 class="card-title mb-0">Blocked Students List</h4>
            </div>

            <div class="card-body">

                <div class="table-responsive database-table">

                    <table class="table table-bordered yajra-datatable">

                        <thead class="table-primary">
                            <tr>
                                <th width="5%">S.No.</th>
                                <th>Examination</th>
                                <th>Student Details</th>
                                <th>Hold Type</th>
                                <th>Semester</th>
                                <th width="18%">Action</th>
                            </tr>
                        </thead>

                        <tbody></tbody>

                    </table>

                </div>

            </div>
        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>
$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | HOLD TYPE - SHOW / HIDE SEMESTER
    |--------------------------------------------------------------------------
    */

    function toggleSemester() {

        let holdType = $('#hold_type').val();

        if (holdType === 'sem-wise') {

            $('#semesterWrapper').show();

            $('#semester_id').prop('required', true);

        } else {

            $('#semesterWrapper').hide();

            $('#semester_id')
                .prop('required', false)
                .val('');

        }
    }

    $('#hold_type').on('change', function () {
        toggleSemester();
    });

    // Handle old input after validation error
    toggleSemester();


    var table;

    $(document).ready(function () {

        table = $('.yajra-datatable').DataTable({

            processing: true,
            serverSide: true,
            destroy: true,

            ajax: {
                url: "{{ route('block-students.data') }}",
                type: "GET"
            },

            columns: [
                {
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'exam_name',
                    name: 'exam.name'
                },
                {
                    data: 'student_details',
                    name: 'student.student_name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'hold_type',
                    name: 'hold_type',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'semester_name',
                    name: 'semester.name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ],

            order: [[1, 'asc']],

            lengthMenu: AppDataTable.lengthMenu,
            pageLength: AppDataTable.pageLength,
            dom: AppDataTable.dom,
            buttons: AppDataTable.buttons,
            drawCallback: AppDataTable.drawCallback

        });

    });


});
</script>

@endpush

