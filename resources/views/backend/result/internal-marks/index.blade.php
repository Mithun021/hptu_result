@extends('backend.partial.master')
@section('title', 'Result Prepare')
@section('sub_title', 'Internal Marks')
@section('backend-content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Internal Marks</h3>
                <div class="export-button">
                    <button type="button" id="export-excel-btn" class="btn btn-sm btn-success">Excel</button>
                    <button type="button" id="overall-internal-print-btn" class="btn btn-sm btn-danger">Print PDF</button>
                </div>
                <div class="card-tools">
                    <a href="{{ route('results.subject-wise.internal-marks') }}" class="btn btn-sm btn-secondary">Subject Wise Internal Marks</a>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Fetch Internal Marks
                    </button>
                </div>

            </div>
            <div class="card-body">
                <div id="internalMarksResult"></div>

            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/libs/jquery-validate/jquery.validate.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@include('backend.result.internal-marks.search-marks-modal')
@include('backend.result.js.search-result')
@include('backend.result.js.fetch-internal-marks')
@include('backend.result.js.export-button')
@endsection
