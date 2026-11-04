@extends('backend.partial.master')

@section('title', 'Result Prepare')
@section('sub_title', 'Result Overview')

@section('backend-content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Result Overview</h3>

                <div class="export-button">
                    <button type="button" id="export-excel-btn" class="btn btn-sm btn-success">Excel</button>
                    <button type="button" id="print-btn-a4" class="btn btn-sm btn-danger">Print PDF</button>
                </div>

                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#resultOverviewOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Search Result
                    </button>
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

@include('backend.result.supplement.result-overview.search-result-overview-modal')
@include('backend.result.supplement.result-overview.search-result-overview')
@include('backend.result.supplement.result-overview.fetch-result-overview-js')
@include('backend.result.js.export-button')
@endsection
