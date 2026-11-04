@extends('backend.partial.master')

@section('title', 'Result Prepare')
@section('sub_title', 'DMC Sheet')

@section('backend-content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">DMC Sheet</h3>
                <div class="export-button">
                    <button type="button" id="compartment-print-btn" class="btn btn-sm btn-danger">Print PDF</button>
                </div>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#dmcOverviewOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Search DMC Sheet
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="resultSheet"></div>

                {{-- @include('backend.result.dmc-sheet.result') --}}

            </div>
        </div>
    </div>
</div>


<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert.js') }}"></script>
<script src="{{ asset('assets/libs/jquery-validate/jquery.validate.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@include('backend.result.dmc-sheet.components.dmc-sheet-modal')
@include('backend.result.dmc-sheet.components.dmc-search-js')
@include('backend.result.dmc-sheet.components.dmc-fetch-js')

@include('backend.result.js.export-button')

@endsection

