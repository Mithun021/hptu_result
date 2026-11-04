@extends('backend.partial.master')
@section('title', 'Result Prepare')
@section('sub_title', 'External Marks')
@section('backend-content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title m-0">Subject Wise External Marks</h4>
            </div>
            <div class="card-header d-flex justify-content-between align-items-center">
                <select name="mark_status" id="mark_status" style="height: 30px; width: 150px;">
                    <option value="">Bulk Action</option>
                    <option value="1">Marks Locked</option>
                    <option value="0">Marks Unlocked</option>
                </select>
                <div class="export-button">
                    <button type="button" id="export-excel-btn" class="btn btn-sm btn-success">Excel</button>
                    <button type="button" id="print-btn-a4" class="btn btn-sm btn-danger">Print PDF</button>
                </div>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#subjectwiseOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Fetch External Marks
                    </button>
                    <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="offcanvas" data-bs-target="#externalMarksImportExportOffcanvasTop">
                        <i data-feather="repeat" style="width:14px; height:14px;"></i> Export/Import
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="subjectwiseExternalMarksResult"></div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/toastify/toastify-js') }}"></script>
<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/libs/jquery-validate/jquery.validate.min.js') }}"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@include('backend.result.external-marks.subject-wise.search-marks-modal')
@include('backend.result.external-marks.subject-wise.download-import-sample')
@include('backend.result.js.search-subjectwise-external-marks-result')
@include('backend.result.js.fetch-subjectwise-external-marks')
@include('backend.result.js.export-button')
@include('backend.result.js.export-external-marks-js')
@endsection