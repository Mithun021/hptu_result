@extends('backend.partial.master')
@section('title', 'Session')
@section('sub_title', 'Edit Session')
@section('backend-content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Edit Session</h4>
            </div>
            <form action="{{ route('sessions.update',$session->id) }}" method="post">
                @csrf
                @method('PUT')
            <div class="card-body px-1">
                <div class="form-group mb-3">
                    <label for="">Session Name<span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name',$session->name ?? '') }}">
                    @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="card-body">
                <button type="submit" class="btn btn-primary">Update Session</button>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection