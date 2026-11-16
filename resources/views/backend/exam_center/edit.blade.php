
@extends('backend.partial.master')

@section('title', 'Edit Exam Center Allocation')
@section('sub_title', 'Edit Exam Center')

@section('backend-content')

<style>
    .checkbox-scroll {
        max-height: 180px;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 10px;
        border: 1px solid #ced4da;
        border-radius: 5px;
    }

    .checkbox-scroll .form-check {
        margin-bottom: 6px;
    }

    .checkbox-scroll .form-check:last-child {
        margin-bottom: 0;
    }
</style>

<div class="row">
    <div class="col-12">

        <div class="card">

            <div class="card-header">
                <h4 class="card-title mb-0">Edit Exam Center Allocation</h4>
            </div>

            <div class="card-body">

                <form action="{{ route('exam-centers.update', $examCenter->id) }}"
                      method="POST">

                    @csrf
                    @method('PUT')

                    <div class="row">

                        {{-- Course Type --}}
                        <div class="d-none">
                            <select name="course_type" id="course_type">
                                <option value="Semester Based" selected>
                                    Semester Based
                                </option>
                            </select>
                        </div>

                        {{-- Student Type --}}
                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Student Type <span class="text-danger">*</span>
                            </label>

                            <select name="student_type"
                                    id="student_type"
                                    class="form-select @error('student_type') is-invalid @enderror">

                                <option value="">--Select Student Type--</option>

                                <option value="Regular"
                                    {{ old('student_type', $examCenter->student_type) == 'Regular' ? 'selected' : '' }}>
                                    Regular
                                </option>

                                <option value="Reappear"
                                    {{ old('student_type', $examCenter->student_type) == 'Reappear' ? 'selected' : '' }}>
                                    Reappear
                                </option>

                                <option value="Special Chance"
                                    {{ old('student_type', $examCenter->student_type) == 'Special Chance' ? 'selected' : '' }}>
                                    Special Chance
                                </option>

                            </select>

                            @error('student_type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                        </div>

                        {{-- Course --}}
                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Course <span class="text-danger">*</span>
                            </label>

                            <select name="course_id"
                                    id="course_id"
                                    class="form-select @error('course_id') is-invalid @enderror">

                                <option value="">--Select Course--</option>

                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}"
                                        {{ old('course_id', $examCenter->course_id) == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                @endforeach

                            </select>

                            @error('course_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                        </div>

                        {{-- Institute: Course Based AJAX --}}
                        <div class="col-md-6 mb-3"
                             id="institute_container"
                             style="{{ old('student_type', $examCenter->student_type) == 'Regular' ? '' : 'display:none;' }}">

                            <label class="form-label">
                                Institute <span class="text-danger">*</span>
                            </label>

                            <select name="institute_id"
                                    id="institute_id"
                                    class="form-select @error('institute_id') is-invalid @enderror">

                                <option value="">--Select Institute--</option>

                            </select>

                            @error('institute_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                        </div>

                        {{-- Schemes --}}
                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Schemes <span class="text-danger">*</span>
                            </label>

                            <div id="scheme_id" class="checkbox-scroll">
                                <span class="text-muted">Loading schemes...</span>
                            </div>

                            @error('schemes')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                            @error('schemes.*')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                        </div>

                        {{-- Branches --}}
                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Branches <span class="text-danger">*</span>
                            </label>

                            <div id="branch_id" class="checkbox-scroll">
                                <span class="text-muted">Loading branches...</span>
                            </div>

                            @error('branches')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                            @error('branches.*')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                        </div>

                        {{-- Semesters --}}
                        <div class="col-md-4 mb-3" id="semester_container">

                            <label class="form-label">
                                Semesters <span class="text-danger">*</span>
                            </label>

                            <div id="semester_id" class="checkbox-scroll">
                                <span class="text-muted">Loading semesters...</span>
                            </div>

                            @error('semesters')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                            @error('semesters.*')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                        </div>

                        {{-- Batches --}}
                        <div class="col-md-3 mb-3">

                            <label class="form-label">
                                Batches <span class="text-danger">*</span>
                            </label>

                            <div id="batch_id" class="checkbox-scroll">
                                <span class="text-muted">Loading batches...</span>
                            </div>

                            @error('batches')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                            @error('batches.*')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                        </div>

                        {{-- Exam Centers: All Institutes --}}
                        <div class="col-md-9 mb-3">

                            <label class="form-label">
                                Exam Centers <span class="text-danger">*</span>
                            </label>

                            <div class="checkbox-scroll">

                                @php
                                    $selectedColleges = old(
                                        'colleges',
                                        $examCenter->colleges->pluck('id')->toArray()
                                    );

                                    $selectedColleges = array_map('strval', $selectedColleges);
                                @endphp

                                @forelse($institutes as $institute)

                                    <div class="form-check">

                                        <input type="checkbox"
                                               class="form-check-input"
                                               name="colleges[]"
                                               id="college_{{ $institute->id }}"
                                               value="{{ $institute->id }}"
                                               {{ in_array((string) $institute->id, $selectedColleges) ? 'checked' : '' }}>

                                        <label class="form-check-label"
                                               for="college_{{ $institute->id }}">
                                            {{ $institute->name }}
                                        </label>

                                    </div>

                                @empty

                                    <span class="text-muted">
                                        No institutes available.
                                    </span>

                                @endforelse

                            </div>

                            @error('colleges')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                            @error('colleges.*')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                        </div>

                    </div>

                    <div class="mt-3">

                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i>
                            Update Exam Center
                        </button>

                        <a href="{{ route('exam-centers.index') }}"
                           class="btn btn-secondary">
                            Cancel
                        </a>

                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

@push('scripts')

<script>
$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | Existing / Old Selected Values
    |--------------------------------------------------------------------------
    */

    let oldSchemes = @json(
        old('schemes', $examCenter->schemes->pluck('id')->toArray())
    );

    let oldBranches = @json(
        old('branches', $examCenter->branches->pluck('id')->toArray())
    );

    let oldSemesters = @json(
        old('semesters', $examCenter->semesters->pluck('id')->toArray())
    );

    let oldBatches = @json(
        old('batches', $examCenter->batches->pluck('id')->toArray())
    );

    let oldInstitute = @json(
        old('institute_id', $examCenter->institute_id)
    );

    oldSchemes = oldSchemes.map(String);
    oldBranches = oldBranches.map(String);
    oldSemesters = oldSemesters.map(String);
    oldBatches = oldBatches.map(String);


    /*
    |--------------------------------------------------------------------------
    | Render Checkboxes
    |--------------------------------------------------------------------------
    */

    function renderCheckboxes(container, items, name, selectedValues = []) {

        let html = '';

        if (!items || items.length === 0) {

            $(container).html(
                '<span class="text-muted">No records available.</span>'
            );

            return;
        }

        $.each(items, function (_, item) {

            let checked = selectedValues.includes(String(item.id))
                ? 'checked'
                : '';

            html += `
                <div class="form-check">

                    <input type="checkbox"
                           class="form-check-input"
                           name="${name}[]"
                           id="${name}_${item.id}"
                           value="${item.id}"
                           ${checked}>

                    <label class="form-check-label"
                           for="${name}_${item.id}">
                        ${item.name}
                    </label>

                </div>
            `;
        });

        $(container).html(html);
    }


    /*
    |--------------------------------------------------------------------------
    | Student Type - Institute Visibility
    |--------------------------------------------------------------------------
    */

    function toggleInstitute() {

        let studentType = $('#student_type').val();

        if (studentType === 'Regular') {

            $('#institute_container').show();
            $('#institute_id').prop('required', true);

        } else {

            $('#institute_container').hide();

            $('#institute_id')
                .prop('required', false)
                .val('');

        }
    }

    $('#student_type').on('change', function () {

        toggleInstitute();

        if ($(this).val() !== 'Regular') {
            oldInstitute = '';
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Reset Course Dependent Fields
    |--------------------------------------------------------------------------
    */

    function resetCourseData() {

        $('#branch_id, #scheme_id, #semester_id, #batch_id').html(
            '<span class="text-muted">Please select course first.</span>'
        );

        $('#institute_id').html(
            '<option value="">--Select Institute--</option>'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Load Data According To Course ID
    |--------------------------------------------------------------------------
    */

    function loadCourseData(courseId, restoreOld = false) {

        if (!courseId) {
            resetCourseData();
            return;
        }

        $('#branch_id, #scheme_id, #semester_id, #batch_id').html(
            '<span class="text-muted">Loading...</span>'
        );

        $('#institute_id').html(
            '<option value="">Loading institutes...</option>'
        );


        /*
        |--------------------------------------------------------------------------
        | Branches AJAX
        |--------------------------------------------------------------------------
        */

        $.get("{{ url('branches') }}/" + courseId)

            .done(function (branches) {

                renderCheckboxes(
                    '#branch_id',
                    branches,
                    'branches',
                    restoreOld ? oldBranches : []
                );

            })

            .fail(function () {

                $('#branch_id').html(
                    '<span class="text-danger">Unable to load branches.</span>'
                );

            });


        /*
        |--------------------------------------------------------------------------
        | Course Details AJAX
        | Schemes, Batches, Semesters, Institutes
        |--------------------------------------------------------------------------
        */

        $.get("{{ url('courses') }}/" + courseId + "/details")

            .done(function (response) {

                // Schemes
                renderCheckboxes(
                    '#scheme_id',
                    response.schemes,
                    'schemes',
                    restoreOld ? oldSchemes : []
                );


                // Batches
                renderCheckboxes(
                    '#batch_id',
                    response.batches,
                    'batches',
                    restoreOld ? oldBatches : []
                );


                // Semesters - Semester Based
                if ($('#course_type').val() === 'Semester Based') {

                    $('#semester_container').show();

                    renderCheckboxes(
                        '#semester_id',
                        response.semesters,
                        'semesters',
                        restoreOld ? oldSemesters : []
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Institute Dropdown - Course ID Based
                |--------------------------------------------------------------------------
                */

                $('#institute_id').empty();

                $('#institute_id').append(
                    '<option value="">--Select Institute--</option>'
                );

                $.each(response.institutes || [], function (_, institute) {

                    let selected = (
                        restoreOld &&
                        String(oldInstitute) === String(institute.id)
                    );

                    $('#institute_id').append(
                        $('<option>', {
                            value: institute.id,
                            text: institute.name,
                            selected: selected
                        })
                    );

                });

            })

            .fail(function () {

                $('#scheme_id, #batch_id, #semester_id').html(
                    '<span class="text-danger">Unable to load course details.</span>'
                );

                $('#institute_id').html(
                    '<option value="">Unable to load institutes.</option>'
                );

            });

    }


    /*
    |--------------------------------------------------------------------------
    | Course Change
    |--------------------------------------------------------------------------
    */

    $('#course_id').on('change', function () {

        // Remove old course-dependent selections
        oldSchemes = [];
        oldBranches = [];
        oldSemesters = [];
        oldBatches = [];
        oldInstitute = '';

        loadCourseData($(this).val(), false);

    });


    /*
    |--------------------------------------------------------------------------
    | Course Type Change
    |--------------------------------------------------------------------------
    */

    $('#course_type').on('change', function () {

        let courseId = $('#course_id').val();

        if (courseId) {
            loadCourseData(courseId, false);
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Initial Page Load
    |--------------------------------------------------------------------------
    */

    toggleInstitute();

    let selectedCourse = $('#course_id').val();

    if (selectedCourse) {
        loadCourseData(selectedCourse, true);
    } else {
        resetCourseData();
    }

});
</script>

@endpush

@endsection
