@extends('backend.partial.master')
@section('title', 'Department')
@section('sub_title', 'Department List')
@section('backend-content')

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title m-0">Add Department</h4>
            </div>
            <form action="{{ route('departments.store') }}" method="post"> @csrf
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                
                <div class="form-group">
                    <label for="">Name<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="Enter department name" value="{{ old('name') }}">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Add Department</button>
            </div>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title m-0">Department List</h4>
            </div>

            <div class="card-body">
                <div class="table-responsive database-table">
                    <table class="table table-bordered" id="responsive-datatable">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th id="no-export">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($departments as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td id="no-export">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-transparent dropdown-toggle" type="button"
                                                    id="actionDropdown{{ $item->id }}" data-bs-toggle="dropdown">
                                                <i class="mdi mdi-dots-vertical fs-16"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-start">
                                                <li>
                                                    <a class="dropdown-item"
                                                    href="{{ route('departments.edit', $item->id) }}">
                                                        <i class="mdi mdi-pencil-outline me-2"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('departments.destroy', $item->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item"
                                                                onclick="return confirm('Delete this department?')">
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