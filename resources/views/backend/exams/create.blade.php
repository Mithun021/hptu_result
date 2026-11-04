@extends('backend.partial.master')
@section('title', 'Exam')
@section('sub_title', 'Add Exam')
@section('backend-content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between mb-3">
                <h4 class="card-title m-0">Create Exams</h4>
                <a href="{{ route('exams.index') }}" class="btn btn-sm btn-danger">Back</a>
            </div>
            <form action="{{ route('exams.store') }}" method="post"> @csrf
            <div class="card-body">
                @include('backend.exams.form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary px-2">Save Exam</button>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection