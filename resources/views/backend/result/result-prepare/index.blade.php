@extends('backend.partial.master')

@section('title', 'Result Prepare')
@section('sub_title', 'End Semester Result Prepare')

@section('backend-content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">End Semester Result Card</h3>
                <div class="export-button">
                    <button type="button" id="export-excel-btn" class="btn btn-sm btn-success">Excel</button>
                    <button type="button" id="print-result-sheet-btn" class="btn btn-sm btn-danger">Print PDF</button>
                </div>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#endSemOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Search End Sem Result
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="endSemMarksResult"></div>
                {{-- @include('backend.result.result-prepare.result') --}}
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/libs/jquery-validate/jquery.validate.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@include('backend.result.result-prepare.search-final-marks-modal')
@include('backend.result.js.search-final-result')
@include('backend.result.js.fetch-end-sem-result')
@include('backend.result.js.export-button')

@endsection
