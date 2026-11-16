
@extends('backend.partial.master')

@section('title', 'Exam Fees')
@section('sub_title', 'Edit Exam Fees')

@section('backend-content')

@php
    $romanOrder = [
        'I' => 1,
        'II' => 2,
        'III' => 3,
        'IV' => 4,
        'V' => 5,
        'VI' => 6,
        'VII' => 7,
        'VIII' => 8,
        'IX' => 9,
        'X' => 10,
        'XI' => 11,
        'XII' => 12,
    ];

    $selectedSchemes = old('schemes', $examFee->schemes->pluck('id')->toArray());
    $selectedBranches = old('branches', $examFee->branches->pluck('id')->toArray());
    $selectedSemesters = old('semesters', $examFee->semesters->pluck('id')->toArray());
    $selectedBatches = old('batches', $examFee->batches->pluck('id')->toArray());
@endphp

<style>
    .checkbox-scroll {
        max-height: 180px;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 10px;
    }

    .checkbox-scroll .form-check {
        margin-bottom: 6px;
    }

    .invalid-checkbox {
        border-color: #dc3545 !important;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">

            {{-- Header --}}
            <div class="card-header d-flex justify-content-between mb-3">
                <h4 class="card-title m-0">Edit Exam Fees</h4>

                <a href="{{ route('exam-fees.index') }}" class="btn btn-sm btn-primary">
                    View Exam Fees
                </a>
            </div>

            <div class="card-body">

                <form action="{{ route('exam-fees.update', $examFee->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        {{-- Course Type --}}
                        <div class="col-md-6 form-group mb-3 d-none">
                            <label for="course_type">Course Type</label>

                            <select name="course_type" id="course_type" class="form-control">
                                <option value="Semester Based" selected>Semester Based</option>
                            </select>
                        </div>

                        {{-- Course --}}
                        <div class="col-md-6 form-group mb-3">
                            <label for="course_id">
                                Select Course <span class="text-danger">*</span>
                            </label>

                            <select name="course_id" id="course_id"
                                class="form-control @error('course_id') is-invalid @enderror">

                                <option value="">--Select Course--</option>

                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}"
                                        {{ old('course_id', $examFee->course_id) == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                @endforeach

                            </select>

                            @error('course_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Schemes --}}
                        <div class="col-md-6 form-group mb-3">
                            <label>
                                Select Schemes <span class="text-danger">*</span>
                            </label>

                            <div class="border rounded p-2 checkbox-scroll
                                {{ $errors->has('schemes') || $errors->has('schemes.*') ? 'invalid-checkbox' : '' }}"
                                id="scheme_id">

                                <small class="text-muted">Please select course first.</small>
                            </div>

                            @error('schemes')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror

                            @foreach ($errors->get('schemes.*') as $messages)
                                @foreach ($messages as $message)
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @endforeach
                            @endforeach
                        </div>

                        {{-- Branches --}}
                        <div class="col-md-4 form-group mb-3">
                            <label>
                                Select Branches <span class="text-danger">*</span>
                            </label>

                            <div class="border rounded p-2 checkbox-scroll
                                {{ $errors->has('branches') || $errors->has('branches.*') ? 'invalid-checkbox' : '' }}"
                                id="branch_id">

                                <small class="text-muted">Please select course first.</small>
                            </div>

                            @error('branches')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror

                            @foreach ($errors->get('branches.*') as $messages)
                                @foreach ($messages as $message)
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @endforeach
                            @endforeach
                        </div>

                        {{-- Semesters --}}
                        <div class="col-md-4 form-group mb-3"
                            id="semester_container"
                            style="display:none;">

                            <label>
                                Select Semesters <span class="text-danger">*</span>
                            </label>

                            <div class="border rounded p-2 checkbox-scroll
                                {{ $errors->has('semesters') || $errors->has('semesters.*') ? 'invalid-checkbox' : '' }}"
                                id="semester_id">

                                <small class="text-muted">Please select course first.</small>
                            </div>

                            @error('semesters')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror

                            @foreach ($errors->get('semesters.*') as $messages)
                                @foreach ($messages as $message)
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @endforeach
                            @endforeach
                        </div>

                        {{-- Annual Semesters --}}
                        <div class="col-md-4 form-group mb-3"
                            id="annual_semester_container"
                            style="display:none;">

                            <label>Select Annual Terms</label>

                            <div class="border rounded p-2 checkbox-scroll"
                                id="annual_semester_id">
                            </div>
                        </div>

                        {{-- Batches --}}
                        <div class="col-md-4 form-group mb-3">
                            <label>
                                Select Sessions / Batches <span class="text-danger">*</span>
                            </label>

                            <div class="border rounded p-2 checkbox-scroll
                                {{ $errors->has('batches') || $errors->has('batches.*') ? 'invalid-checkbox' : '' }}"
                                id="batch_id">

                                <small class="text-muted">Please select course first.</small>
                            </div>

                            @error('batches')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror

                            @foreach ($errors->get('batches.*') as $messages)
                                @foreach ($messages as $message)
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @endforeach
                            @endforeach
                        </div>

                        {{-- Exam Fees --}}
                        <div class="col-md-12 form-group mb-3">
                            <div class="table-responsive">
                                <table class="table table-bordered">

                                    <thead>
                                        <tr>
                                            <th>Student Type</th>
                                            <th>Exam Type</th>
                                            <th>Exam Fee</th>
                                            <th>Max Exam Fee</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        {{-- Regular --}}
                                        <tr>
                                            <td>Regular</td>
                                            <td>All</td>

                                            <td>
                                                <input type="number"
                                                    class="form-control @error('regular_exam_fee') is-invalid @enderror"
                                                    name="regular_exam_fee"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ old('regular_exam_fee', $examFee->regular_exam_fee) }}">

                                                @error('regular_exam_fee')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>

                                            <td></td>
                                        </tr>

                                        {{-- Reappear Theory --}}
                                        <tr>
                                            <td>Reappear</td>
                                            <td>Theory</td>

                                            <td>
                                                <input type="number"
                                                    class="form-control @error('reappear_theory_exam_fee') is-invalid @enderror"
                                                    name="reappear_theory_exam_fee"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ old('reappear_theory_exam_fee', $examFee->reappear_theory_exam_fee) }}">

                                                @error('reappear_theory_exam_fee')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                <input type="number"
                                                    class="form-control @error('reappear_theory_max_exam_fee') is-invalid @enderror"
                                                    name="reappear_theory_max_exam_fee"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ old('reappear_theory_max_exam_fee', $examFee->reappear_theory_max_exam_fee) }}">

                                                @error('reappear_theory_max_exam_fee')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>
                                        </tr>

                                        {{-- Reappear Practical --}}
                                        <tr>
                                            <td></td>
                                            <td>Practical</td>

                                            <td>
                                                <input type="number"
                                                    class="form-control @error('reappear_practical_exam_fee') is-invalid @enderror"
                                                    name="reappear_practical_exam_fee"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ old('reappear_practical_exam_fee', $examFee->reappear_practical_exam_fee) }}">

                                                @error('reappear_practical_exam_fee')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                <input type="number"
                                                    class="form-control @error('reappear_practical_max_exam_fee') is-invalid @enderror"
                                                    name="reappear_practical_max_exam_fee"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ old('reappear_practical_max_exam_fee', $examFee->reappear_practical_max_exam_fee) }}">

                                                @error('reappear_practical_max_exam_fee')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>
                                        </tr>

                                        {{-- Special Chance Theory --}}
                                        <tr>
                                            <td>Special Chance</td>
                                            <td>Theory</td>

                                            <td>
                                                <input type="number"
                                                    class="form-control @error('special_chance_theory_exam_fee') is-invalid @enderror"
                                                    name="special_chance_theory_exam_fee"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ old('special_chance_theory_exam_fee', $examFee->special_chance_theory_exam_fee) }}">

                                                @error('special_chance_theory_exam_fee')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                <input type="number"
                                                    class="form-control @error('special_chance_theory_max_exam_fee') is-invalid @enderror"
                                                    name="special_chance_theory_max_exam_fee"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ old('special_chance_theory_max_exam_fee', $examFee->special_chance_theory_max_exam_fee) }}">

                                                @error('special_chance_theory_max_exam_fee')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>
                                        </tr>

                                        {{-- Special Chance Practical --}}
                                        <tr>
                                            <td></td>
                                            <td>Practical</td>

                                            <td>
                                                <input type="number"
                                                    class="form-control @error('special_chance_practical_exam_fee') is-invalid @enderror"
                                                    name="special_chance_practical_exam_fee"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ old('special_chance_practical_exam_fee', $examFee->special_chance_practical_exam_fee) }}">

                                                @error('special_chance_practical_exam_fee')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                <input type="number"
                                                    class="form-control @error('special_chance_practical_max_exam_fee') is-invalid @enderror"
                                                    name="special_chance_practical_max_exam_fee"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ old('special_chance_practical_max_exam_fee', $examFee->special_chance_practical_max_exam_fee) }}">

                                                @error('special_chance_practical_max_exam_fee')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Exam Start Date --}}
                        <div class="col-md-6 form-group mb-3">
                            <label for="exam_start_date">
                                Exam Start Date <span class="text-danger">*</span>
                            </label>

                            <input type="date"
                                name="exam_start_date"
                                id="exam_start_date"
                                class="form-control @error('exam_start_date') is-invalid @enderror"
                                value="{{ old('exam_start_date', $examFee->exam_start_date?->format('Y-m-d')) }}">

                            @error('exam_start_date')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Exam End Date --}}
                        <div class="col-md-6 form-group mb-3">
                            <label for="exam_end_date">
                                Exam End Date <span class="text-danger">*</span>
                            </label>

                            <input type="date"
                                name="exam_end_date"
                                id="exam_end_date"
                                class="form-control @error('exam_end_date') is-invalid @enderror"
                                value="{{ old('exam_end_date', $examFee->exam_end_date?->format('Y-m-d')) }}">

                            @error('exam_end_date')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Late Fees --}}
                        <div class="col-md-12 form-group mb-3">
                            <div class="table-responsive">
                                <table class="table table-bordered">

                                    <thead>
                                        <tr>
                                            <th colspan="3">
                                                <h4 class="mb-0">Late Fees</h4>
                                            </th>
                                        </tr>

                                        <tr>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Late Fee Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        {{-- Late Fee 1 --}}
                                        <tr>
                                            <td>
                                                <input type="date"
                                                    class="form-control @error('late_fee_start1') is-invalid @enderror"
                                                    name="late_fee_start1"
                                                    value="{{ old('late_fee_start1', $examFee->late_fee_start1?->format('Y-m-d')) }}">

                                                @error('late_fee_start1')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                <input type="date"
                                                    class="form-control @error('late_fee_end1') is-invalid @enderror"
                                                    name="late_fee_end1"
                                                    value="{{ old('late_fee_end1', $examFee->late_fee_end1?->format('Y-m-d')) }}">

                                                @error('late_fee_end1')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                <input type="number"
                                                    class="form-control @error('late_fee_amount1') is-invalid @enderror"
                                                    name="late_fee_amount1"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ old('late_fee_amount1', $examFee->late_fee_amount1) }}">

                                                @error('late_fee_amount1')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>
                                        </tr>

                                        {{-- Late Fee 2 --}}
                                        <tr>
                                            <td>
                                                <input type="date"
                                                    class="form-control @error('late_fee_start2') is-invalid @enderror"
                                                    name="late_fee_start2"
                                                    value="{{ old('late_fee_start2', $examFee->late_fee_start2?->format('Y-m-d')) }}">

                                                @error('late_fee_start2')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                <input type="date"
                                                    class="form-control @error('late_fee_end2') is-invalid @enderror"
                                                    name="late_fee_end2"
                                                    value="{{ old('late_fee_end2', $examFee->late_fee_end2?->format('Y-m-d')) }}">

                                                @error('late_fee_end2')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                <input type="number"
                                                    class="form-control @error('late_fee_amount2') is-invalid @enderror"
                                                    name="late_fee_amount2"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ old('late_fee_amount2', $examFee->late_fee_amount2) }}">

                                                @error('late_fee_amount2')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>
                                        </tr>

                                        {{-- Late Fee 3 --}}
                                        <tr>
                                            <td>
                                                <input type="date"
                                                    class="form-control @error('late_fee_start3') is-invalid @enderror"
                                                    name="late_fee_start3"
                                                    value="{{ old('late_fee_start3', $examFee->late_fee_start3?->format('Y-m-d')) }}">

                                                @error('late_fee_start3')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                <input type="date"
                                                    class="form-control @error('late_fee_end3') is-invalid @enderror"
                                                    name="late_fee_end3"
                                                    value="{{ old('late_fee_end3', $examFee->late_fee_end3?->format('Y-m-d')) }}">

                                                @error('late_fee_end3')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                <input type="number"
                                                    class="form-control @error('late_fee_amount3') is-invalid @enderror"
                                                    name="late_fee_amount3"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ old('late_fee_amount3', $examFee->late_fee_amount3) }}">

                                                @error('late_fee_amount3')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="col-md-12 form-group mb-3">

                            <button type="submit" class="btn btn-primary">
                                Update Exam Fee
                            </button>

                            <a href="{{ route('exam-fees.index') }}"
                                class="btn btn-secondary">
                                Cancel
                            </a>

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

    // Existing selected values or old values after validation error
    let oldSchemes = @json($selectedSchemes);
    let oldBranches = @json($selectedBranches);
    let oldSemesters = @json($selectedSemesters);
    let oldBatches = @json($selectedBatches);

    // Normalize IDs to strings
    oldSchemes = oldSchemes.map(String);
    oldBranches = oldBranches.map(String);
    oldSemesters = oldSemesters.map(String);
    oldBatches = oldBatches.map(String);

    function resetSemesterUI() {
        $('#semester_container').hide();
        $('#annual_semester_container').hide();

        $('#semester_id').html('');
        $('#annual_semester_id').html('');
    }

    // Generate Checkbox HTML
    function renderCheckboxes(container, items, name, oldValues, labelField = 'name') {

        let html = '';

        if (!items || items.length === 0) {

            html = '<small class="text-muted">No records found.</small>';

        } else {

            $.each(items, function (_, item) {

                let checked = oldValues.includes(String(item.id)) ? 'checked' : '';

                html += `
                    <div class="form-check mb-1">
                        <input type="checkbox"
                            class="form-check-input"
                            name="${name}[]"
                            id="${name}_${item.id}"
                            value="${item.id}"
                            ${checked}>

                        <label class="form-check-label" for="${name}_${item.id}">
                            ${item[labelField]}
                        </label>
                    </div>
                `;
            });
        }

        $(container).html(html);
    }

    function loadCourseData(courseId) {

        if (!courseId) {

            $('#scheme_id, #branch_id, #batch_id').html(
                '<small class="text-muted">Please select course first.</small>'
            );

            resetSemesterUI();
            return;
        }

        resetSemesterUI();

        $('#branch_id, #scheme_id, #batch_id').html(
            '<small class="text-muted">Please wait...</small>'
        );

        // Fetch Branches
        $.get("{{ url('branches') }}/" + courseId, function (branches) {

            renderCheckboxes(
                '#branch_id',
                branches,
                'branches',
                oldBranches
            );

        });

        // Fetch Course Details
        $.get("{{ url('courses') }}/" + courseId + "/details", function (response) {

            // Schemes
            renderCheckboxes(
                '#scheme_id',
                response.schemes,
                'schemes',
                oldSchemes
            );

            // Batches
            renderCheckboxes(
                '#batch_id',
                response.batches,
                'batches',
                oldBatches,
                'name'
            );

            let courseType = $('#course_type').val();

            // Semester Based
            if (courseType === 'Semester Based') {

                $('#semester_container').show();

                renderCheckboxes(
                    '#semester_id',
                    response.semesters,
                    'semesters',
                    oldSemesters
                );
            }

            // Annual Based
            if (courseType === 'Annual Based') {

                $('#annual_semester_container').show();

                renderCheckboxes(
                    '#annual_semester_id',
                    response.annual_semesters,
                    'annual_semesters',
                    [],
                    'name'
                );
            }

        });
    }

    // Course Change
    $('#course_id').on('change', function () {

        // Clear previous selections when course changes
        oldSchemes = [];
        oldBranches = [];
        oldSemesters = [];
        oldBatches = [];

        loadCourseData($(this).val());
    });

    // Initial Load - Existing Course
    let defaultCourse = $('#course_id').val();

    if (defaultCourse) {
        loadCourseData(defaultCourse);
    }

});
</script>
@endpush

@endsection
