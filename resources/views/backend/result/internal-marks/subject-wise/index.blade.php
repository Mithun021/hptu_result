@extends('backend.partial.master')
@section('title', 'Result Prepare')
@section('sub_title', 'Internal Marks')
@section('backend-content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <select name="mark_status" id="mark_status" style="height: 30px; width: 150px;">

                        <option value="">Bulk Action</option>

                        <option value="1">Marks Locked</option>

                        <option value="0">Marks Unlocked</option>

                    </select>

                    <button
                        type="button"
                        id="update-lock-status"
                        class="btn btn-sm btn-primary"
                    >
                        Update Status
                    </button>

                </div>
                <div class="export-button">
                    <button type="button" id="export-excel-btn" class="btn btn-sm btn-success">Excel</button>
                    <button type="button" id="print-btn-a4" class="btn btn-sm btn-danger">Print PDF</button>
                </div>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#subjectwiseOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Fetch Internal Marks
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="subjectwiseInternalMarksResult"></div>
                
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/libs/jquery-validate/jquery.validate.min.js') }}"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@include('backend.result.internal-marks.subjectwise-search-marks-modal')
@include('backend.result.js.search-subjectwise-result')
@include('backend.result.js.fetch-subjectwise-internal-marks')
@include('backend.result.js.export-button')
@endsection