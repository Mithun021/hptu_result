@extends('backend.partial.master')
@section('title', 'Department')
@section('sub_title', 'Edit Department')
@section('backend-content')


<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title m-0">Edit Department</h4>
            </div>
            <form action="{{ route('departments.update', $department->id) }}" method="post"> @csrf @method('PUT')
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                
                <div class="form-group">
                    <label for="">Name<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" placeholder="Enter department name" value="{{ old('name', $department->name) }}">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update Department</button>
            </div>
            </form>
        </div>
    </div>
</div>

@endsection