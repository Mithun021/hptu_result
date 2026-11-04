@extends('backend.partial.master')
@section('title', 'Pass Percentage')
@section('sub_title', 'Pass Percentage List')
@section('backend-content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Add New Pass Percentage</h4>
            </div>
            <form action="{{ route('pass-percentages.store') }}" method="post">
                @csrf
            <div class="card-body px-1">
                <div class="form-group mb-3">
                    <label for="">Course<span class="text-danger">*</span></label>
                    <select name="course_id" class="form-control">
                        <option value="">--Select--</option>
                    @foreach ($courses as $item)
                        <option value="{{ $item->id }}"
                        {{ old('course_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                    @endforeach
                    </select>
                    @error('course_id')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group mb-3">
                    <label for="">Percentage<span class="text-danger">*</span></label>
                    <input type="number" name="overall_pass_percentage" class="form-control" value="{{ old('overall_pass_percentage') }}">
                    @error('overall_pass_percentage')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group mb-3">
                    <label for="">Theory min Percentage<span class="text-danger">*</span></label>
                    <input type="number" name="theory_min_percentage" class="form-control" value="{{ old('theory_min_percentage') }}">
                    @error('theory_min_percentage')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="card-body">
                <button type="submit" class="btn btn-primary">Add Scheme</button>
            </div>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Pass Percentage List</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive database-table">
                    <table class="table table-bordered table-bordered"  id="responsive-datatable">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Course</th>
                                <th>Overall Percentage(%)</th>
                                <th>Theory min Percentage(%)</th>
                                <th scope="col" id="no-export">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($passPercentages as $index => $item)
                            <tr>
                                <td>{{ ++$index }}</td>
                                <td>{{ $item->course->name ?? '' }}</td>
                                <td>{{ $item->overall_pass_percentage }}</td>
                                <td>{{ $item->theory_min_percentage }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-transparent dropdown-toggle" type="button" 
                                                id="actionDropdown{{ $item->id }}" data-bs-toggle="dropdown" 
                                                aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical fs-16"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="actionDropdown{{ $item->id }}">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('pass-percentages.edit', $item->id) }}">
                                                    <i class="mdi mdi-pencil-outline me-2"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('pass-percentages.destroy', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item" onclick="return confirm('Delete this branch?')">
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