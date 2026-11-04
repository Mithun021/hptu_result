@extends('backend.partial.master')
@section('title', 'Marks Grade')
@section('sub_title', 'Marks Grade List')
@section('backend-content')

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Add New Marks Grade</h4>
            </div>

            <form action="{{ route('marks-grades.store') }}" method="post">
                @csrf

                <div class="card-body px-1">
                    <div class="form-group mb-3">
                        <label>Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-control">
                            <option value="">--Select--</option>
                            @foreach ($courses as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('course_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label>Grade Name <span class="text-danger">*</span></label>
                        <input type="text" name="grade_name" class="form-control"
                               value="{{ old('grade_name') }}" placeholder="A+, A, B+">
                        @error('grade_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label>Percent From <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="percent_from" class="form-control"
                               value="{{ old('percent_from') }}">
                        @error('percent_from') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label>Percent Upto <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="percent_upto" class="form-control"
                               value="{{ old('percent_upto') }}">
                        @error('percent_upto') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- <div class="form-group mb-3">
                        <label>Grade Point <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="grade_point" class="form-control"
                               value="{{ old('grade_point') }}">
                        @error('grade_point') <span class="text-danger">{{ $message }}</span> @enderror
                    </div> --}}

                    <div class="form-group mb-3">
                        <label>Grade Description</label>
                        <input type="text" name="grade_description" maxlength="20"
                               class="form-control" value="{{ old('grade_description') }}"
                               placeholder="Excellent / Good">
                        @error('grade_description') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="card-body">
                    <button type="submit" class="btn btn-primary">Add Grade</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Marks Grade List</h4>
            </div>

            <div class="card-body">
                <div class="table-responsive database-table">
                    <table class="table table-bordered" id="responsive-datatable">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Course</th>
                                <th>Grade</th>
                                <th>From</th>
                                <th>Upto</th>
                                {{-- <th>Point</th> --}}
                                <th>Description</th>
                                <th id="no-export">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($marksGrades as $index => $item)
                            <tr>
                                <td>{{ ++$index }}</td>
                                <td>{{ $item->course->name ?? '' }}</td>
                                <td>{{ $item->grade_name }}</td>
                                <td>{{ $item->percent_from }}</td>
                                <td>{{ $item->percent_upto }}</td>
                                {{-- <td>{{ $item->grade_point }}</td> --}}
                                <td>{{ $item->grade_description }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-transparent dropdown-toggle" type="button"
                                                id="actionDropdown{{ $item->id }}" data-bs-toggle="dropdown">
                                            <i class="mdi mdi-dots-vertical fs-16"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-start">
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('marks-grades.edit', $item->id) }}">
                                                    <i class="mdi mdi-pencil-outline me-2"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('marks-grades.destroy', $item->id) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item"
                                                            onclick="return confirm('Delete this grade?')">
                                                        <i class="mdi mdi-delete-outline me-2"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
