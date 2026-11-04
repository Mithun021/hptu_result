@extends('backend.partial.master')
@section('title', 'Retotalling Result')
@section('sub_title', 'Mapped Subjects')
@section('backend-content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Mapped Subjects</h4>
                    
                    <div class="export-button">
                        <button type="button" id="sheet-print-btn" class="btn btn-sm btn-danger">Print PDF</button>
                    </div>

                    <div class="buttondata">
                        <a href="{{ route('retotal-result.mapped-subjects.create') }}" class="btn btn-sm btn-primary">
                            <i data-feather="plus" style="width:14px; height:14px;"></i> Add Mapped Subject</a>
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="offcanvas" data-bs-target="#searchMappedSubjectOffcanvasTop">
                            <i data-feather="filter" style="width:14px; height:14px;"></i> Search
                        </button>

                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="resultSheet"></div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert.js') }}"></script>

@include('backend.retotalling-result.mapped-subjects.components.search-data-modal')
@include('backend.retotalling-result.mapped-subjects.components.js.search-form-data')
@include('backend.retotalling-result.mapped-subjects.components.js.fetch-student-mapped-subjects')
@include('backend.retotalling-result.mapped-subjects.components.js.export-js')

@endsection
