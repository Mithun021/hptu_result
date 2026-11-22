@extends('backend.partial.master')
@section('title', 'Result Prepare')
@section('sub_title', 'External Marks')
@section('backend-content')
<style>
    table.result-table {
        width: 100%;
        border-collapse: collapse;
    }

    table.result-table th,
    table.result-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
        font-size: 10px;
    }

    /* Nested table fix */
    table.result-table table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        margin: 0;
    }

    table.result-table table th,
    table.result-table table td {
        border: 1px solid #ddd;
        padding: 4px;
        font-size: 10px;
    }
</style>
<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">External Marks</h3>
            <div class="export-button d-flex gap-2">
                <select name="mark_status" id="mark_status">
                    <option value="">Bulk Action</option>
                    <option value="1">Marks Locked</option>
                    <option value="0">Marks Unlocked</option>
                </select>
                <button type="button" id="export-excel-btn" class="btn btn-sm btn-success">Excel</button>
                <button type="button" id="extenal-marks-print-btn" class="btn btn-sm btn-danger">Print PDF</button>
                {{-- <button type="button" id="print-nc-btn" class="btn btn-sm btn-warning">Print NC Course</button> --}}
            </div>
            <div class="card-tools">
                <a href="{{ route('results.subject-wise.external-marks') }}" class="btn btn-sm btn-secondary">Subject-wise External Marks</a>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#externalmarksoffcanvasTop">
                    <i data-feather="filter" style="width:14px; height:14px;"></i> Fetch External Marks
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="externalMarksResult"></div>
            {{-- @include('backend.result.external-marks.annual-result') --}}
        </div>
    </div>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/libs/jquery-validate/jquery.validate.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@include('backend.result.external-marks.search-marks-modal')
@include('backend.result.js.search-result')
@include('backend.result.js.fetch-external-marks')
@include('backend.result.js.export-button')
@endsection
