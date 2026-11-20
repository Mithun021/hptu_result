@extends('backend.partial.master')
@section('title', 'Edit Marks Grade')
@section('sub_title', 'Edit Marks Grade')
@section('backend-content')

<style>
    .marks-grade-card {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        overflow: hidden;
    }

    .marks-grade-card .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        padding: 14px 18px;
    }

    .marks-grade-card .card-header h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }

    .marks-grade-card .card-body {
        padding: 20px;
    }

    .marks-grade-card label {
        font-weight: 500;
        margin-bottom: 7px;
    }

    .marks-grade-card .form-group {
        margin-bottom: 18px;
    }

    .marks-grade-card .form-control {
        min-height: 40px;
    }

    .grade-section-title {
        font-size: 14px;
        font-weight: 600;
        color: #344054;
        padding-bottom: 10px;
        margin-bottom: 18px;
        border-bottom: 1px solid #e5e7eb;
    }
</style>

<div class="row">

    <div class="col-md-12">

        <div class="card marks-grade-card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Edit Marks Grade</h4>
                <span class="text-muted small">
                    Update Course & Scheme Wise Grade Configuration
                </span>
            </div>

            <form action="{{ route('marks-grades.update', $marksGrade->id) }}"
                  method="POST">

                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="grade-section-title">
                        Grade Configuration Details
                    </div>

                    <div class="row">

                        {{-- Course --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    Course <span class="text-danger">*</span>
                                </label>

                                <select name="course_id" class="form-control" required>
                                    <option value="">-- Select Course --</option>

                                    @foreach ($courses as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('course_id', $marksGrade->course_id) == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('course_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- Scheme --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    Scheme <span class="text-danger">*</span>
                                </label>

                                <select name="scheme_id" class="form-control" required>
                                    <option value="">-- Select Scheme --</option>

                                    @foreach ($schemes as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('scheme_id', $marksGrade->scheme_id) == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('scheme_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- Grade Name --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    Grade Name <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="grade_name"
                                       class="form-control"
                                       value="{{ old('grade_name', $marksGrade->grade_name) }}"
                                       maxlength="20"
                                       placeholder="Example: A+, A, B+"
                                       required>

                                @error('grade_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- Percent From --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    Percent From <span class="text-danger">*</span>
                                </label>

                                <input type="number"
                                       name="percent_from"
                                       class="form-control"
                                       value="{{ old('percent_from', $marksGrade->percent_from) }}"
                                       min="0"
                                       max="100"
                                       step="0.01"
                                       placeholder="Example: 80.00"
                                       required>

                                @error('percent_from')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- Percent Upto --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    Percent Upto <span class="text-danger">*</span>
                                </label>

                                <input type="number"
                                       name="percent_upto"
                                       class="form-control"
                                       value="{{ old('percent_upto', $marksGrade->percent_upto) }}"
                                       min="0"
                                       max="100"
                                       step="0.01"
                                       placeholder="Example: 89.99"
                                       required>

                                @error('percent_upto')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- Grade Point --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    Grade Point <span class="text-danger">*</span>
                                </label>

                                <input type="number"
                                       name="grade_point"
                                       class="form-control"
                                       value="{{ old('grade_point', $marksGrade->grade_point) }}"
                                       min="0"
                                       step="0.01"
                                       placeholder="Example: 9.00"
                                       required>

                                @error('grade_point')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- Grade Description --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Grade Description</label>

                                <input type="text"
                                       name="grade_description"
                                       class="form-control"
                                       value="{{ old('grade_description', $marksGrade->grade_description) }}"
                                       maxlength="20"
                                       placeholder="Excellent / Good">

                                @error('grade_description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <div class="d-flex justify-content-between mt-3">

                        <a href="{{ route('marks-grades.index') }}"
                           class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i>
                            Back
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline me-1"></i>
                            Update Grade
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
