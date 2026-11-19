 @extends('backend.partial.master')

@section('title', 'Edit Examination Form')
@section('sub_title', 'Edit Examination Form')

@section('backend-content')

<style>
    .exam-edit-wrapper {
        background: #fff;
        border: 1px solid #e5e7eb;
        padding: 15px;
        border-radius: 4px;
    }

    .exam-info-table {
        width: 100%;
        margin-bottom: 0;
    }

    .exam-info-table td {
        padding: 7px 10px;
        font-size: 12px;
        border: 1px solid #e5e7eb;
    }

    .exam-info-table td:first-child {
        width: 180px;
        font-weight: 600;
        background: #f8f9fa;
    }

    .section-title {
        font-size: 14px;
        font-weight: 700;
        padding: 10px 12px;
        background: #f1f3f5;
        border: 1px solid #dee2e6;
        margin: 18px 0 0;
    }

    .subject-table {
        width: 100%;
        margin-bottom: 0;
    }

    .subject-table th {
        background: #f1f3f5;
        font-size: 11px;
        text-transform: uppercase;
        padding: 9px;
        white-space: nowrap;
    }

    .subject-table td {
        font-size: 12px;
        padding: 8px 9px;
        vertical-align: middle;
    }

    .subject-table .subject-name {
        font-weight: 600;
        color: #212529;
    }

    .subject-checkbox {
        width: 17px;
        height: 17px;
        cursor: pointer;
    }

    .subject-table tbody tr:hover {
        background: #f8f9fa;
    }

    .subject-count {
        font-size: 12px;
        color: #6c757d;
    }

    .exam-center-select {
        height: 38px;
        font-size: 13px;
    }

    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>

<div class="exam-edit-wrapper">

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success no-print">
            {{ session('success') }}
        </div>
    @endif

    {{-- VALIDATION ERRORS --}}
    @if($errors->any())
        <div class="alert alert-danger no-print">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- PAGE ACTIONS --}}
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h5 class="mb-0">Edit Examination Form</h5>

        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    {{-- STUDENT DETAILS --}}
    <div class="section-title">
        Student Details
    </div>

    <div class="table-responsive">
        <table class="table table-bordered exam-info-table">
            <tbody>
                <tr>
                    <td>Student Name</td>
                    <td>{{ $examinationForm->student->student_name ?? '-' }}</td>
                </tr>

                <tr>
                    <td>Father Name</td>
                    <td>{{ $examinationForm->student->father_name ?? '-' }}</td>
                </tr>

                <tr>
                    <td>University Roll No.</td>
                    <td>{{ $examinationForm->student->university_roll_no ?? '-' }}</td>
                </tr>

                <tr>
                    <td>College / Institute</td>
                    <td>{{ $examinationForm->institute->name ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- EXAMINATION DETAILS --}}
    <div class="section-title">
        Examination Details
    </div>

    <div class="table-responsive">
        <table class="table table-bordered exam-info-table">
            <tbody>
                <tr>
                    <td>Exam Name</td>
                    <td>{{ $examinationForm->exam->name ?? '-' }}</td>
                </tr>

                <tr>
                    <td>Exam Type</td>
                    <td>{{ $examinationForm->exam_type ?? '-' }}</td>
                </tr>

                <tr>
                    <td>Form ID</td>
                    <td>{{ $examinationForm->form_id ?? '-' }}</td>
                </tr>

                <tr>
                    <td>Course</td>
                    <td>{{ $examinationForm->course->name ?? '-' }}</td>
                </tr>

                <tr>
                    <td>Scheme</td>
                    <td>{{ $examinationForm->scheme->name ?? '-' }}</td>
                </tr>

                <tr>
                    <td>Branch</td>
                    <td>{{ $examinationForm->branch->name ?? '-' }}</td>
                </tr>

                <tr>
                    <td>Semester</td>
                    <td>{{ $examinationForm->semester->name ?? '-' }}</td>
                </tr>

                <tr>
                    <td>Batch</td>
                    <td>{{ $examinationForm->batch->name ?? '-' }}</td>
                </tr>

                <tr>
                    <td>Current Exam Center</td>
                    <td>{{ $examinationForm->examCenter->name ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <form action="{{ route('admin.examination-form.update', $examinationForm->id) }}"
          method="POST">

        @csrf
        @method('PUT')

        {{-- UPDATE EXAM CENTER --}}
        <div class="section-title">
            Update Exam Center
        </div>

        <div class="row py-3">

            <div class="col-md-12">

                <label for="exam_center_id" class="font-weight-bold"
                       style="font-size:12px;">
                    Select Exam Center <span class="text-danger">*</span>
                </label>

                <select name="exam_center_id"
                        id="exam_center_id"
                        class="form-control exam-center-select"
                        required>

                    <option value="">Select Exam Center</option>

                    @foreach($examCenters as $center)
                        <option value="{{ $center->id }}"
                            {{ (string) old('exam_center_id', $examinationForm->exam_center_id) === (string) $center->id ? 'selected' : '' }}>
                            {{ $center->name }}
                            @if($center->city)
                                - {{ $center->city }}
                            @endif
                        </option>
                    @endforeach

                </select>

                @error('exam_center_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror

            </div>

        </div>

        {{-- SUBJECT SELECTION --}}
        <div class="section-title">
            Subjects Applied
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap py-3">

            <div class="subject-count">
                Total Mapped Subjects:
                <strong>{{ $mappedSubjects->count() }}</strong>
                |
                Selected Subjects:
                <strong id="selectedSubjectCount">
                    {{ count(old('subject_ids', $selectedSubjectIds)) }}
                </strong>
            </div>

            <div class="no-print">
                <label class="mb-0" style="font-size:12px; cursor:pointer;">
                    <input type="checkbox" id="selectAllSubjects">
                    Select All
                </label>
            </div>

        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover subject-table">

                <thead>
                    <tr>
                        <th width="6%" class="text-center">Select</th>
                        <th width="7%">S.No.</th>
                        <th width="15%">Subject Code</th>
                        <th>Subject Name</th>
                        <th width="15%">Subject Type</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($mappedSubjects as $index => $mappedSubject)

                        @php
                            $subject = $mappedSubject->subject;

                            $checkedSubjectIds = old(
                                'subject_ids',
                                $selectedSubjectIds
                            );

                            $isChecked = in_array(
                                $subject->id,
                                $checkedSubjectIds
                            );
                        @endphp

                        <tr>
                            <td class="text-center">
                                <input
                                    type="checkbox"
                                    class="subject-checkbox"
                                    name="subject_ids[]"
                                    value="{{ $subject->id }}"
                                    {{ $isChecked ? 'checked' : '' }}
                                >
                            </td>

                            <td>{{ $index + 1 }}</td>

                            <td>{{ $subject->subject_code ?? '-' }}</td>

                            <td class="subject-name">
                                {{ $subject->name ?? '-' }}
                            </td>

                            <td>{{ $subject->type ?? '-' }}</td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center text-danger py-3">
                                No mapped subjects found for this student.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        {{-- FORM BUTTONS --}}
        <div class="text-right mt-3 no-print">

            <a href="{{ url()->previous() }}"
               class="btn btn-secondary btn-sm">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save"></i>
                Update Examination Form
            </button>

        </div>

    </form>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const selectAll = document.getElementById('selectAllSubjects');
        const subjectCheckboxes = document.querySelectorAll('.subject-checkbox');
        const selectedCount = document.getElementById('selectedSubjectCount');

        function updateSelectedCount() {

            const checkedCount = document.querySelectorAll(
                '.subject-checkbox:checked'
            ).length;

            selectedCount.textContent = checkedCount;

            selectAll.checked =
                subjectCheckboxes.length > 0 &&
                checkedCount === subjectCheckboxes.length;

            selectAll.indeterminate =
                checkedCount > 0 &&
                checkedCount < subjectCheckboxes.length;
        }

        selectAll.addEventListener('change', function () {

            subjectCheckboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });

            updateSelectedCount();
        });

        subjectCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        updateSelectedCount();
    });
</script>

@endsection
