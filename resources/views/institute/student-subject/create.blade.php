@extends('institute.partial.master')
@section('title', 'Student Subjects')
@section('sub_title', 'Add Student Subjects')
@section('institute-content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Add Student Subject</h5>

                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasTop">
                    <i data-feather="filter" style="width:14px; height:14px;"></i> Search Student
                </button>
            </div>
            <div class="card-body">
                <div class="student-data"></div>
                
            </div>
        </div>
    </div>
</div>

@include('institute.student-subject.search-student-modal')
@include('institute.student-subject.script')

@endsection