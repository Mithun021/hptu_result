@extends('backend.partial.master')
@section('title', 'Branch')
@section('sub_title', 'Branch List')
@section('backend-content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title m-0">Add New Branch</h4>
            </div>
            <form action="{{ route('branches.store') }}" method="post">
                @csrf
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-12 mb-3">
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
                    <div class="form-group col-md-6 mb-3">
                        <label for="">Branch Name<span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="">Branch Code</label>
                        <input type="text" name="branch_code" class="form-control" value="{{ old('branch_code') }}">
                        @error('branch_code')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-12 form-group mb-2">
                        <label>Institute <span class="text-danger">*</span></label>
                        <div class="scroll-box">
                            @foreach($institutes as $institute)
                                <div>
                                    <input type="checkbox" name="institute[]" value="{{ $institute->id }}"
                                        {{ in_array($institute->id, old('institute', [])) ? 'checked' : '' }}>
                                    {{ $institute->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <button type="submit" class="btn btn-primary">Add Branch</button>
            </div>
            </form>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Branch List</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive database-table">
                    <table class="table table-bordered table-bordered"  id="responsive-datatable">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Course</th>
                                <th>Branch Name</th>
                                <th>Code</th>
                                <th>Institutes</th>
                                <th scope="col" id="no-export">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($branches as $index => $item)
                            <tr>
                                <td>{{ ++$index }}</td>
                                <td>{{ $item->course->name ?? '' }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->branch_code ?? '' }}</td>
                                <td>
                                    @forelse($item->institutes as $inst)
                                        <span class="badge bg-secondary me-1">{{ $inst->name }}</span>
                                    @empty
                                        <span class="text-muted">Not Assigned</span>
                                    @endforelse
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-transparent dropdown-toggle" type="button" 
                                                id="actionDropdown{{ $item->id }}" data-bs-toggle="dropdown" 
                                                aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical fs-16"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="actionDropdown{{ $item->id }}">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('branches.edit', $item->id) }}">
                                                    <i class="mdi mdi-pencil-outline me-2"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('branches.destroy', $item->id) }}" method="POST" class="d-inline">
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