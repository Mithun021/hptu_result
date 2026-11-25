
<style>
    .result-container {
        width: 100%;
        margin: auto;
        font-family: Arial, Helvetica, sans-serif;
    }

    .result-header {
        text-align: center;
        margin-bottom: 10px;
    }

    .result-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: bold;
    }

    .result-header h4 {
        margin: 2px 0;
        font-size: 16px;
    }

    .result-header p {
        margin: 4px 0;
        font-size: 14px;
    }

    .course-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
        font-size: 12px;
    }

    .result-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .result-table th,
    .result-table td {
        border: 1px solid #000;
        padding: 6px;
        text-align: center;
        vertical-align: top;
    }

    .result-table th {
        background: #f2f2f2;
        font-weight: bold;
    }

    .result-table td.student-name {
        text-align: left;
    }

    td.align-left {
        text-align: left !important;
    }

    td.align-right {
        text-align: right;
    }

    .form-check {
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        gap: 8px;
        margin-bottom: 5px;
    }

    .form-check-input {
        margin: 2px 0 0 0;
        flex-shrink: 0;
    }

    .form-check-label {
        margin: 0;
        text-align: left;
        cursor: pointer;
    }
</style>

@if($students->count())

<form id="suppleStudentForm">

    {{-- Hidden Filters --}}
    <input type="hidden" name="exam_id" value="{{ request('exam_id') }}">
    <input type="hidden" name="course_type" value="{{ request('course_type') }}">
    <input type="hidden" name="course_id" value="{{ request('course_id') }}">
    <input type="hidden" name="scheme_id" value="{{ request('scheme_id') }}">
    <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
    <input type="hidden" name="semester_id" value="{{ request('semester_id') }}">
    <input type="hidden" name="annual_semester_id" value="{{ request('annual_semester_id') }}">
    <input type="hidden" name="batch_id" value="{{ request('batch_id') }}">
    <input type="hidden" name="institute_id" value="{{ request('institute_id') }}">

    <div class="table-responsive">

        <table class="result-table result-table-a4">

            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Father Name</th>
                    <th>Mother Name</th>
                    <th>Reappear Subjects</th>
                </tr>
            </thead>

            <tbody>

                @foreach($students as $studentId => $subjects)

                    @php
                        $student = $subjects->first()->student;

                        $selectedSubjects = $checkedSubjects[$studentId] ?? [];
                    @endphp

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            {{ $student->university_roll_no }}
                        </td>

                        <td class="student-name">
                            {{ $student->student_name }}
                        </td>

                        <td class="align-left">
                            {{ $student->father_name }}
                        </td>

                        <td class="align-left">
                            {{ $student->mother_name }}
                        </td>

                        <td class="align-left">

                            <input
                                type="hidden"
                                name="students[]"
                                value="{{ $student->id }}"
                            >

                            @foreach($subjects as $row)

                                @php
                                    $subjectId = $row->subject->id;
                                @endphp

                                <div class="form-check">

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="subject_ids[{{ $student->id }}][]"
                                        value="{{ $subjectId }}"
                                        id="subject{{ $student->id }}{{ $subjectId }}"

                                        {{ in_array($subjectId, $selectedSubjects) ? 'checked' : '' }}
                                    >

                                    <label
                                        class="form-check-label align-left"
                                        for="subject{{ $student->id }}{{ $subjectId }}"
                                    >
                                        {{ $row->subject->subject_code }}
                                        -
                                        {{ $row->subject->name }}
                                    </label>

                                </div>

                            @endforeach

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

    <div class="text-center mt-3">

        <button
            type="button"
            class="btn btn-success"
            id="saveSuppleStudents">

            Save Reappear Subjects

        </button>

    </div>

</form>

@else

    <div class="alert alert-danger">
        No Reappear Students Found.
    </div>

@endif


<script>

    $("#saveSuppleStudents").click(function () {

        let checked = $("#suppleStudentForm input[type=checkbox]:checked").length;

        if (checked == 0) {

            Swal.fire(
                "Warning",
                "Please select at least one subject.",
                "warning"
            );

            return;
        }

        Swal.fire({

            title: 'Are you sure?',

            text: 'Do you want to save selected reappear subjects?',

            icon: 'question',

            showCancelButton: true,

            confirmButtonText: 'Yes Save'

        }).then((result) => {

            if (result.isConfirmed) {

                saveSupple();

            }

        });

    });


    function saveSupple() {

        $.ajax({

            url: "{{ route('results.reappear.student.save') }}",

            type: "POST",

            data: $("#suppleStudentForm").serialize(),

            headers: {
                'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
            },

            beforeSend: function () {

                $("#saveSuppleStudents").prop('disabled', true);

            },

            success: function (res) {

                Swal.fire(
                    'Success',
                    res.message,
                    'success'
                );

            },

            error: function (xhr) {

                let message = xhr.responseJSON?.message
                    || 'Something went wrong while saving subjects.';

                Swal.fire(
                    'Error',
                    message,
                    'error'
                );

            },

            complete: function () {

                $("#saveSuppleStudents").prop('disabled', false);

            }

        });

    }

</script>
