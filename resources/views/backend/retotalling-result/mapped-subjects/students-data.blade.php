
@if($students->isEmpty())

    <div class="alert alert-warning text-center">
        <i data-feather="alert-circle"></i>
        No student found for the selected filters.
    </div>

@elseif($subjects->isEmpty())

    <div class="alert alert-warning text-center">
        No Theory Subjects found for the selected course and semester.
    </div>

@else

    <style>
        .student-result-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 22px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .student-result-header {
            background: #f5f7fb;
            padding: 15px 18px;
            border-bottom: 1px solid #e5e7eb;
        }

        .student-result-header h5 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #344054;
        }

        .student-info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            padding: 18px;
            background: #fff;
        }

        .student-info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .student-info-item .info-label {
            font-size: 11px;
            color: #858b98;
            text-transform: uppercase;
            font-weight: 600;
        }

        .student-info-item .info-value {
            font-size: 13px;
            color: #344054;
            font-weight: 600;
        }

        .student-subject-wrapper {
            padding: 0 18px 18px;
        }

        .student-subject-wrapper .table {
            margin-bottom: 0;
            vertical-align: middle;
        }

        .student-subject-wrapper .table th {
            background: #f8f9fc;
            font-size: 12px;
            color: #667085;
            font-weight: 600;
            white-space: nowrap;
        }

        .student-subject-wrapper .table td {
            font-size: 13px;
            color: #344054;
        }

        .student-subject-wrapper .form-check-input {
            cursor: pointer;
            width: 16px;
            height: 16px;
        }

        .student-subject-title {
            font-size: 14px;
            font-weight: 600;
            color: #344054;
            margin-bottom: 12px;
        }

        @media(max-width: 767px) {
            .student-info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    <form id="retotallingSubjectForm">

        @csrf

        <input type="hidden" name="exam_id"
            value="{{ $validated['exam_id'] }}">

        <input type="hidden" name="result_type"
            value="{{ $validated['result_type'] }}">

        <input type="hidden" name="course_type"
            value="{{ $validated['course_type'] }}">

        <input type="hidden" name="course_id"
            value="{{ $validated['course_id'] }}">

        <input type="hidden" name="scheme_id"
            value="{{ $validated['scheme_id'] ?? '' }}">

        <input type="hidden" name="branch_id"
            value="{{ $validated['branch_id'] }}">

        <input type="hidden" name="batch_id"
            value="{{ $validated['batch_id'] }}">

        <input type="hidden" name="institute_id"
            value="{{ $validated['institute_id'] }}">

        <input type="hidden" name="semester_id"
            value="{{ $validated['semester_id'] ?? '' }}">

        <input type="hidden" name="annual_semester_id"
            value="{{ $validated['annual_semester_id'] ?? '' }}">

        @foreach($students as $index => $student)

            <input type="hidden"
                name="student_ids[]"
                value="{{ $student->id }}">

            @php
                $mappedIds = $student->mapped_subject_ids ?? [];

                $subjectIds = $subjects->pluck('id')->all();

                $selectedCount = count(
                    array_intersect($subjectIds, $mappedIds)
                );

                $allChecked = $subjects->isNotEmpty()
                    && $selectedCount === $subjects->count();
            @endphp

            <div class="student-result-card">

                {{-- Student Header --}}
                <div class="student-result-header d-flex justify-content-between align-items-center">

                    <h5>
                        <i data-feather="user" style="width:16px;"></i>
                        Student {{ $index + 1 }}
                    </h5>

                    <span class="badge bg-primary">
                        {{ $student->university_roll_no }}
                    </span>

                </div>

                {{-- Student Details --}}
                <div class="student-info-grid">

                    <div class="student-info-item">
                        <span class="info-label">University Roll No.</span>
                        <span class="info-value">
                            {{ $student->university_roll_no }}
                        </span>
                    </div>

                    <div class="student-info-item">
                        <span class="info-label">Student Name</span>
                        <span class="info-value">
                            {{ $student->student_name }}
                        </span>
                    </div>

                    <div class="student-info-item">
                        <span class="info-label">Father's Name</span>
                        <span class="info-value">
                            {{ $student->father_name }}
                        </span>
                    </div>

                    <div class="student-info-item">
                        <span class="info-label">Course</span>
                        <span class="info-value">
                            {{ $student->course->name ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="student-info-item">
                        <span class="info-label">
                            {{ $validated['course_type'] === 'Semester Based'
                                ? 'Semester'
                                : 'Annual Term' }}
                        </span>

                        <span class="info-value">
                            {{ $validated['course_type'] === 'Semester Based'
                                ? ($student->semester->name ?? 'N/A')
                                : ($student->annual_semester->name ?? 'N/A') }}
                        </span>
                    </div>

                    <div class="student-info-item">
                        <span class="info-label">Batch / Session</span>
                        <span class="info-value">
                            {{ $student->batch->name ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="student-info-item">
                        <span class="info-label">Branch</span>
                        <span class="info-value">
                            {{ $student->branch->name ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="student-info-item">
                        <span class="info-label">Scheme</span>
                        <span class="info-value">
                            {{ $student->scheme->name ?? 'N/A' }}
                        </span>
                    </div>

                </div>

                {{-- Subjects Table --}}
                <div class="student-subject-wrapper">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div class="student-subject-title">
                            <i data-feather="book-open"
                                style="width:16px;"></i>
                            Theory Subjects
                        </div>

                        <span class="badge bg-light text-dark">
                            Selected: {{ $selectedCount }}/{{ $subjects->count() }}
                        </span>

                    </div>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover">

                            <thead>
                                <tr>
                                    <th width="65" class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input
                                                type="checkbox"
                                                class="form-check-input student-select-all"
                                                data-student="{{ $student->id }}"
                                                {{ $allChecked ? 'checked' : '' }}
                                            >
                                        </div>
                                    </th>

                                    <th width="80">S.No.</th>

                                    <th>Subject Name</th>

                                    <th width="180">Subject Code</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($subjects as $subject)

                                    <tr>

                                        <td class="text-center">

                                            <input
                                                type="checkbox"
                                                class="form-check-input student-subject-checkbox"
                                                name="students[{{ $student->id }}][subjects][]"
                                                value="{{ $subject->id }}"
                                                data-student="{{ $student->id }}"
                                                {{ in_array($subject->id, $mappedIds) ? 'checked' : '' }}
                                            >

                                        </td>

                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <strong>{{ $subject->name }}</strong>
                                        </td>

                                        <td>
                                            {{ $subject->subject_code ?? 'N/A' }}
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        @endforeach

        {{-- SAVE BUTTON --}}
        <div class="d-flex justify-content-end mb-4 mt-3">

            <button type="button"
                class="btn btn-primary px-4"
                id="saveRetotallingSubjects">

                <i data-feather="save" class="me-1"></i>
                Save Re-totaling Subjects

            </button>

        </div>

    </form>

@endif


<script>
    $(document).ready(function () {
        /*
        |--------------------------------------------------------------------------
        | SAVE RE-TOTALLING STUDENT SUBJECTS
        |--------------------------------------------------------------------------
        */

        $(document).on('click', '#saveRetotallingSubjects', function () {

            let button = $(this);

            let form = $('#retotallingSubjectForm');

            if (!form.length) {
                return;
            }

            let formData = form.serialize();

            button.prop('disabled', true);

            button.html(`
                <span class="spinner-border spinner-border-sm me-1"></span>
                Saving...
            `);

            $.ajax({

                url: "{{ route('retotal-result.mapped-subjects.save-student-subject') }}",

                type: "POST",

                data: formData,

                success: function (response) {

                    if (response.status) {

                        Toastify({
                            text: response.message,
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            close: true,
                            backgroundColor: "#28a745"
                        }).showToast();

                    } else {

                        Toastify({
                            text: response.message || "Something went wrong!",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            close: true,
                            backgroundColor: "#dc3545"
                        }).showToast();

                    }

                },

                error: function (xhr) {

                    let message = "Something went wrong while saving!";

                    if (xhr.status === 422) {

                        let errors = xhr.responseJSON.errors;

                        if (errors) {
                            message = Object.values(errors).flat()[0];
                        }

                    } else if (xhr.responseJSON && xhr.responseJSON.message) {

                        message = xhr.responseJSON.message;

                    }

                    Toastify({
                        text: message,
                        duration: 4000,
                        gravity: "top",
                        position: "right",
                        close: true,
                        backgroundColor: "#dc3545"
                    }).showToast();

                },

                complete: function () {

                    button.prop('disabled', false);

                    button.html(`
                        <i data-feather="save" class="me-1"></i>
                        Save Re-totaling Subjects
                    `);

                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }

                }

            });

        });



        // Select All Subjects - Student Wise
        $(document).on('change', '.student-select-all', function () {

            let studentId = $(this).data('student');

            let checked = $(this).is(':checked');

            $('.student-subject-checkbox[data-student="' + studentId + '"]')
                .prop('checked', checked);

        });

        // Individual Subject Selection
        $(document).on('change', '.student-subject-checkbox', function () {

            let studentId = $(this).data('student');

            let total = $('.student-subject-checkbox[data-student="' + studentId + '"]').length;

            let selected = $('.student-subject-checkbox[data-student="' + studentId + '"]:checked').length;

            let selectAll = $('.student-select-all[data-student="' + studentId + '"]');

            selectAll.prop('checked', total > 0 && total === selected);

            selectAll.prop('indeterminate', selected > 0 && selected < total);

        });

    });
</script>
