@extends('backend.partial.master')
@section('title', 'Marks Grade')
@section('sub_title', 'Marks Grade List')
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

    .grade-action {
        white-space: nowrap;
    }
</style>

<div class="row">

    {{-- ================= ADD MARKS GRADE FORM ================= --}}
    <div class="col-md-12">
        <div class="card marks-grade-card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Add New Marks Grade</h4>
                <span class="text-muted small">Course & Scheme Wise Grade Configuration</span>
            </div>

            <form action="{{ route('marks-grades.store') }}" method="POST">
                @csrf

                <div class="card-body">

                    <div class="grade-section-title">
                        Grade Configuration Details
                    </div>

                    <div class="row">

                        {{-- Course --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Course <span class="text-danger">*</span></label>
                                <select name="course_id" class="form-control" required>
                                    <option value="">-- Select Course --</option>
                                    @foreach ($courses as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('course_id') == $item->id ? 'selected' : '' }}>
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
                                <label>Scheme <span class="text-danger">*</span></label>
                                <select name="scheme_id" class="form-control" required>
                                    <option value="">-- Select Scheme --</option>
                                    @foreach ($schemes as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('scheme_id') == $item->id ? 'selected' : '' }}>
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
                                <label>Grade Name <span class="text-danger">*</span></label>
                                <input type="text"
                                    name="grade_name"
                                    class="form-control"
                                    value="{{ old('grade_name') }}"
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
                                <label>Percent From <span class="text-danger">*</span></label>
                                <input type="number"
                                    name="percent_from"
                                    class="form-control"
                                    value="{{ old('percent_from') }}"
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
                                <label>Percent Upto <span class="text-danger">*</span></label>
                                <input type="number"
                                    name="percent_upto"
                                    class="form-control"
                                    value="{{ old('percent_upto') }}"
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
                                <label>Grade Point <span class="text-danger">*</span></label>
                                <input type="number"
                                    name="grade_point"
                                    class="form-control"
                                    value="{{ old('grade_point') }}"
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
                                    value="{{ old('grade_description') }}"
                                    maxlength="20"
                                    placeholder="Excellent / Good">
                                @error('grade_description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <div class="d-flex justify-content-end mt-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-plus-circle-outline me-1"></i>
                            Add Grade
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>


    {{-- ================= MARKS GRADE LIST ================= --}}
    <div class="col-md-12 mt-3">
        <div class="card marks-grade-card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Marks Grade List</h4>
                <span class="text-muted small">
                    Total Grades: {{ $marksGrades->count() }}
                </span>
            </div>

            <div class="card-body">

                <div class="table-responsive database-table">

                    <table class="table table-bordered table-hover align-middle"
                           id="responsive-datatable">

                        <thead class="table-primary">
                            <tr>
                                <th width="60">#</th>
                                <th>Course</th>
                                <th>Scheme</th>
                                <th>Grade</th>
                                <th>From (%)</th>
                                <th>Upto (%)</th>
                                <th>Grade Point</th>
                                <th>Description</th>
                                <th id="no-export" width="90">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($marksGrades as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>

                                    <td>
                                        {{ $item->course->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $item->scheme->name ?? '-' }}
                                    </td>

                                    <td>
                                        <strong>{{ $item->grade_name }}</strong>
                                    </td>

                                    <td>{{ number_format($item->percent_from, 2) }}</td>

                                    <td>{{ number_format($item->percent_upto, 2) }}</td>

                                    <td>
                                        {{ $item->grade_point !== null
                                            ? number_format($item->grade_point, 2)
                                            : '-' }}
                                    </td>

                                    <td>{{ $item->grade_description ?? '-' }}</td>

                                    <td class="grade-action">
                                        <div class="dropdown">

                                            <button class="btn btn-sm btn-transparent dropdown-toggle"
                                                    type="button"
                                                    id="actionDropdown{{ $item->id }}"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false">

                                                <i class="mdi mdi-dots-vertical fs-16"></i>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-start">

                                                <li>
                                                    <a class="dropdown-item"
                                                       href="{{ route('marks-grades.edit', $item->id) }}">

                                                        <i class="mdi mdi-pencil-outline me-2"></i>
                                                        Edit
                                                    </a>
                                                </li>

                                                <li>
                                                    <form action="{{ route('marks-grades.destroy', $item->id) }}"
                                                          method="POST"
                                                          class="d-inline">

                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                                class="dropdown-item"
                                                                onclick="return confirm('Are you sure you want to delete this grade?')">

                                                            <i class="mdi mdi-delete-outline me-2"></i>
                                                            Delete
                                                        </button>

                                                    </form>
                                                </li>

                                            </ul>
                                        </div>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        No marks grades found.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>
        </div>
    </div>

</div>

@endsection
