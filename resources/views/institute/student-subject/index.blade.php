@extends('institute.partial.master')
@section('title', 'Student Subjects')
@section('sub_title', 'Student Subjects List')
@section('institute-content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Student Subjects</h5>
                <div class="button">
                    <a href="{{ route('student-subject.create') }}" class="btn btn-sm btn-primary ms-auto">Add/Update Student Subject</a>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Filter
                    </button>
                    <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target=".bs-example-modal-lg">
                        <i data-feather="upload" style="width:14px; height:14px;"></i> Import Data
                    </button>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="offcanvas" data-bs-target="#exportSampleoffcanvasTop">
                        <i data-feather="download" style="width:14px; height:14px;"></i> Export Sample
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive database-table">
                    <table class="table table-bordered yajra-datatable w-100">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Roll no</th>
                                <th>Father's Name</th>
                                <th>Course</th>
                                <th>Branch</th>
                                <th>Semester/Terms</th>
                                <th>Subjects</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Import Subjects</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <form action="{{ route('student-subject.import') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Choose File</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>


@include('institute.student-subject.export-sample-modal')
@include('institute.student-subject.filter-modal')
@include('institute.student-subject.script')

@endsection