@extends('institute.partial.master')
@section('title', 'Faculty')
@section('sub_title', 'Edit Faculties')
@section('institute-content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title m-0">Edit Teacher</h4>
                <a href="{{ route('teachers.index') }}" class="btn btn-sm btn-danger">
                    <i data-feather="arrow-left" class="me-1" style="width:14px;height:14px;"></i> Back
                </a>
            </div>
            <form action="{{ route('teachers.update', $institute->id) }}" method="post">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="">Teacher Name<span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" 
                               value="{{ old('name', $institute->name) }}">
                        @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="">Teacher Sort Name</label>
                        <input type="text" name="sort_name" class="form-control" 
                               value="{{ old('sort_name', $institute->sort_name) }}">
                        @error('sort_name')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="">Phone</label>
                        <input type="text" name="phone" class="form-control" 
                               value="{{ old('phone', $institute->phone) }}">
                        @error('phone')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="">Email<span class="text-danger">*</span></label>
                        <input type="text" name="email" class="form-control" 
                               value="{{ old('email', $institute->email) }}">
                        @error('email')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="">Password <small class="text-muted">(Leave blank to keep current password)</small></label>
                        <input type="password" name="password" class="form-control">
                        @error('password')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Update Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
