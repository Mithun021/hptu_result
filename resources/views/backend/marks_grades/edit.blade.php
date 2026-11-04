@extends('backend.partial.master')
@section('title', 'Edit Marks Grade')
@section('sub_title', 'Edit Marks Grade')
@section('backend-content')

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title m-0">Edit Marks Grade</h4>
            </div>

            <form action="{{ route('marks-grades.update', $marksGrade->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body px-2">
                    <div class="form-group mb-3">
                        <label>Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-control">
                            <option value="">--Select--</option>
                            @foreach ($courses as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('course_id', $marksGrade->course_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label>Grade Name <span class="text-danger">*</span></label>
                        <input type="text" name="grade_name" class="form-control"
                               value="{{ old('grade_name', $marksGrade->grade_name) }}">
                        @error('grade_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label>Percent From <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="percent_from" class="form-control"
                               value="{{ old('percent_from', $marksGrade->percent_from) }}">
                        @error('percent_from') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label>Percent Upto <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="percent_upto" class="form-control"
                               value="{{ old('percent_upto', $marksGrade->percent_upto) }}">
                        @error('percent_upto') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- <div class="form-group mb-3">
                        <label>Grade Point <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="grade_point" class="form-control"
                               value="{{ old('grade_point', $marksGrade->grade_point) }}">
                        @error('grade_point') <span class="text-danger">{{ $message }}</span> @enderror
                    </div> --}}

                    <div class="form-group mb-3">
                        <label>Grade Description</label>
                        <input type="text" name="grade_description" maxlength="20"
                               class="form-control"
                               value="{{ old('grade_description', $marksGrade->grade_description) }}">
                        @error('grade_description') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="card-body d-flex justify-content-between">
                    <a href="{{ route('marks-grades.index') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Update Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection