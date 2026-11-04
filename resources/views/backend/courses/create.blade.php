@extends('backend.partial.master')
@section('title', 'Courses')
@section('sub_title', 'Add New Courses')
@section('backend-content')
<style>
    .scroll-box {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 4px;
        background: #fff;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="m-0">Course List</h4>
                <a href="{{ route('courses.index') }}" class="btn btn-sm btn-dark">Back</a>
            </div>
            <form action="{{ route('courses.store') }}" method="POST">
                @csrf

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-12 form-group mb-2 d-none">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="course" value="UG"
                                    {{ old('course') == 'UG' ? 'checked' : '' }} checked>
                                <label class="form-check-label">UG</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="course" value="Diploma"
                                    {{ old('course') == 'Diploma' ? 'checked' : '' }}>
                                <label class="form-check-label">Diploma</label>
                            </div>

                            @error('course')
                                <br><small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Course Name --}}
                        <div class="col-md-12 form-group mb-2">
                            <label>Course Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Course Code --}}
                        <div class="col-md-6 form-group mb-2">
                            <label>Course Code</label>
                            <input type="text" name="course_code" class="form-control" value="{{ old('course_code') }}">
                        </div>

                        {{-- Course Type --}}
                        <div class="col-md-6 form-group mb-2 d-none">
                            <label>Course Type <span class="text-danger">*</span></label>
                            <select name="course_type" id="course_type" class="form-control">
                                {{-- <option value="">-- Select --</option> --}}
                                {{-- <option value="Annual Based">Annual Based</option> --}}
                                <option value="Semester Based" default selected>Semester Based</option>
                            </select>
                            @error('course_type') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Semester --}}
                        <div class="col-md-4 form-group mb-2" id="semester_container" style="display: none;">
                            <label>Semester</label>
                            <div class="scroll-box">
                                @foreach($semesters as $semester)
                                    <div>
                                        <input type="checkbox" name="semester[]" value="{{ $semester->id }}">
                                        {{ $semester->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Annual Semester --}}
                        <div class="col-md-4 form-group mb-2" id="annual_semester_container" style="display: none;">
                            <label>Annual Terms</label>
                            <div class="scroll-box">
                                @foreach($annual_semesters as $semester)
                                    <div>
                                        <input type="checkbox" name="annual_semester[]" value="{{ $semester->id }}">
                                        {{ $semester->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Scheme --}}
                        <div class="col-md-4 form-group mb-2">
                            <label>Scheme <span class="text-danger">*</span></label>
                            <div class="scroll-box">
                                @foreach($schemes as $scheme)
                                    <div>
                                        <input type="checkbox" name="scheme[]" value="{{ $scheme->id }}">
                                        {{ $scheme->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Session / Batch --}}
                        <div class="col-md-4 form-group mb-2">
                            <label>Session / Batch <span class="text-danger">*</span></label>
                            <div class="scroll-box">
                                @foreach($batches as $batch)
                                    <div>
                                        <input type="checkbox" name="batch[]" value="{{ $batch->id }}">
                                        {{ $batch->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Institute --}}
                        <div class="col-md-12 form-group mb-2">
                            <label>Institute <span class="text-danger">*</span></label>
                            <div class="scroll-box">
                                @foreach($institutes as $institute)
                                    <div class="">
                                        <input type="checkbox" name="institute[]" value="{{ $institute->id }}">
                                        {{ $institute->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>


                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Save Course</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const courseType = document.getElementById('course_type');
    const semesterContainer = document.getElementById('semester_container');
    const annualContainer = document.getElementById('annual_semester_container');

    function resetCheckboxes(container) {
        container.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
    }

    function toggleContainers() {
        if (courseType.value === 'Semester Based') {
            semesterContainer.style.display = 'block';
            annualContainer.style.display = 'none';
            resetCheckboxes(annualContainer);
        }
        else if (courseType.value === 'Annual Based') {
            annualContainer.style.display = 'block';
            semesterContainer.style.display = 'none';
            resetCheckboxes(semesterContainer);
        }
        else {
            semesterContainer.style.display = 'none';
            annualContainer.style.display = 'none';
            resetCheckboxes(semesterContainer);
            resetCheckboxes(annualContainer);
        }
    }

    // On page load (validation error / edit page)
    toggleContainers();

    // On change
    courseType.addEventListener('change', toggleContainers);
});
</script>


@endsection
