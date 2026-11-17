
@extends('backend.partial.master')

@section('title', 'Edit Block Student')
@section('sub_title', 'Edit Block Student')

@section('backend-content')

<div class="row">

    <div class="col-12">

        <div class="card">

            <div class="card-header">
                <h4 class="card-title mb-0">Edit Block Student</h4>
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

                <form action="{{ route('block-students.update', $blockStudent->id) }}"
                      method="POST">

                    @csrf
                    @method('PUT')

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
                                        {{ old('exam_id', $blockStudent->exam_id) == $exam->id ? 'selected' : '' }}>
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
                                    {{ old('hold_type', $blockStudent->hold_type) == 'overall' ? 'selected' : '' }}>
                                    Overall
                                </option>

                                <option value="sem-wise"
                                    {{ old('hold_type', $blockStudent->hold_type) == 'sem-wise' ? 'selected' : '' }}>
                                    Sem-Wise
                                </option>

                            </select>

                        </div>

                    </div>


                    {{-- SECOND ROW --}}
                    <div class="row">

                        {{-- UNIVERSITY ROLL NUMBER --}}
                        <div class="col-md-6 mb-3">

                            <label for="university_roll_no" class="form-label">
                                University Roll Number
                            </label>

                            <input type="text"
                                   id="university_roll_no"
                                   class="form-control"
                                   value="{{ $blockStudent->student->university_roll_no ?? '' }}"
                                   readonly>

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
                                        {{ old('semester_id', $blockStudent->semester_id) == $semester->id ? 'selected' : '' }}>
                                        {{ $semester->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                    </div>


                    {{-- BUTTONS --}}
                    <div class="row">

                        <div class="col-12 d-flex justify-content-end gap-2">

                            <a href="{{ route('block-students.index') }}"
                               class="btn btn-secondary">
                                Back
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i>
                                Update Block Student
                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>


@push('scripts')

<script>
$(document).ready(function () {

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

    // Initial state on page load
    toggleSemester();

});
</script>

@endpush

@endsection
