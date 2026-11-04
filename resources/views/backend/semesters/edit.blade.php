@extends('backend.partial.master')
@section('title', 'Semester')
@section('sub_title', 'Edit Semester')
@section('backend-content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Edit Semester</h4>
            </div>
            <form action="{{ route('semesters.update',$semester->id) }}" method="post">
                @csrf
                @method('PUT')
            <div class="card-body px-1">
                <div class="form-group mb-3">
                    <label for="">Semester Name<span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name',$semester->name ?? '') }}">
                    @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="card-body">
                <button type="submit" class="btn btn-primary">Update Semester</button>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection