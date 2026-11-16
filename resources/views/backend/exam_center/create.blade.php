
@extends('backend.partial.master')

@section('title', 'Exam Center Allocation')
@section('sub_title', 'Add Exam Center')

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
                <h4 class="card-title mb-0">Add Exam Center Allocation</h4>
            </div>

            <div class="card-body">

                <form action="{{ route('exam-centers.store') }}" method="POST">
                    @csrf

                    <div class="row">

                        {{-- Course Type --}}
                        <div class="d-none">
                            <select name="course_type" id="course_type">
                                <option value="Semester Based" selected>Semester Based</option>
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
                                    {{ old('student_type') == 'Regular' ? 'selected' : '' }}>
                                    Regular
                                </option>

                                <option value="Reappear"
                                    {{ old('student_type') == 'Reappear' ? 'selected' : '' }}>
                                    Reappear
                                </option>

                                <option value="Special Chance"
                                    {{ old('student_type') == 'Special Chance' ? 'selected' : '' }}>
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
                                        {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                @endforeach

                            </select>

                            @error('course_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                        </div>

                        {{-- Institute: Course ID Based --}}
                        <div class="col-md-12 mb-3"
                             id="institute_container"
                             style="display:none;">

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
                                <span class="text-muted">Please select course first.</span>
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
                                <span class="text-muted">Please select course first.</span>
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
                                <span class="text-muted">Please select course first.</span>
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
                                <span class="text-muted">Please select course first.</span>
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

                                @forelse($institutes as $institute)

                                    <div class="form-check">

                                        <input type="checkbox"
                                               class="form-check-input"
                                               name="colleges[]"
                                               id="college_{{ $institute->id }}"
                                               value="{{ $institute->id }}"
                                               {{ in_array($institute->id, old('colleges', [])) ? 'checked' : '' }}>

                                        <label class="form-check-label"
                                               for="college_{{ $institute->id }}">
                                            {{ $institute->name }}
                                        </label>

                                    </div>

                                @empty

                                    <span class="text-muted">No institutes available.</span>

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
                            Save Exam Center
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

    // Old selected values
    let oldSchemes   = @json(old('schemes', []));
    let oldBranches  = @json(old('branches', []));
    let oldSemesters = @json(old('semesters', []));
    let oldBatches   = @json(old('batches', []));
    let oldInstitute = "{{ old('institute_id') }}";

    oldSchemes   = oldSchemes.map(String);
    oldBranches  = oldBranches.map(String);
    oldSemesters = oldSemesters.map(String);
    oldBatches   = oldBatches.map(String);

    /*
    |--------------------------------------------------------------------------
    | Render Checkboxes
    |--------------------------------------------------------------------------
    */

    function renderCheckboxes(container, items, name, oldValues = []) {

        let html = '';

        if (!items || items.length === 0) {
            $(container).html(
                '<span class="text-muted">No records available.</span>'
            );
            return;
        }

        $.each(items, function (_, item) {

            let checked = oldValues.includes(String(item.id))
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

            // Required only for Regular students
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
    });


    /*
    |--------------------------------------------------------------------------
    | Reset Course Dependent Data
    |--------------------------------------------------------------------------
    */

    function resetCourseData() {

        $('#branch_id').html(
            '<span class="text-muted">Please select course first.</span>'
        );

        $('#scheme_id').html(
            '<span class="text-muted">Please select course first.</span>'
        );

        $('#semester_id').html(
            '<span class="text-muted">Please select course first.</span>'
        );

        $('#batch_id').html(
            '<span class="text-muted">Please select course first.</span>'
        );

        $('#institute_id').html(
            '<option value="">--Select Institute--</option>'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Load Course Dependent Data
    |--------------------------------------------------------------------------
    */

    function loadCourseData(courseId, restoreOld = false) {

        if (!courseId) {
            resetCourseData();
            return;
        }

        // Loading messages
        $('#branch_id, #scheme_id, #semester_id, #batch_id').html(
            '<span class="text-muted">Loading...</span>'
        );

        $('#institute_id').html(
            '<option value="">Loading institutes...</option>'
        );


        /*
        |--------------------------------------------------------------------------
        | Fetch Branches By Course ID
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
        | Fetch Course Details
        | Schemes, Batches, Semesters and Institutes
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


                // Semesters - Semester Based Only
                let courseType = $('#course_type').val();

                if (courseType === 'Semester Based') {

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
                | Institutes - Filtered By Selected Course ID
                |--------------------------------------------------------------------------
                */

                $('#institute_id').html(
                    '<option value="">--Select Institute--</option>'
                );

                $.each(response.institutes || [], function (_, institute) {

                    let selected = restoreOld &&
                        String(oldInstitute) === String(institute.id);

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
                    '<option value="">Unable to load institutes</option>'
                );

            });

    }


    /*
    |--------------------------------------------------------------------------
    | Course Change
    |--------------------------------------------------------------------------
    */

    $('#course_id').on('change', function () {

        // Clear previous course selections
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

    let defaultCourse = $('#course_id').val();

    if (defaultCourse) {
        loadCourseData(defaultCourse, true);
    }

});
</script>

@endpush

@endsection
