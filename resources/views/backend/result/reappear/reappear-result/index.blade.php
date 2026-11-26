@extends('backend.partial.master')
@section('title', 'Reappear Result')
@section('sub_title', 'Result Prepare')
@section('backend-content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                {{-- <h3 class="card-title">Prepare Supplementary Result</h3> --}}
                <div class="d-flex gap-2">
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

                </div>

                <div class="export-button">
                    <button type="button" id="export-excel-btn" class="btn btn-sm btn-success">Excel</button>
                    <button type="button" id="print-btn-a4" class="btn btn-sm btn-danger">Print PDF</button>
                </div>

                <div class="card-tools">
                    {{-- <a href="{{ route('results.end-sem.result-prepare') }}" target="_blank" class="btn btn-sm btn-success"><i data-feather="check-circle" style="width:14px; height:14px;"></i> Supple Final Result Report</a> --}}
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#endSemOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Search Result
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="endSemMarksResult"></div>
                {{-- @include('backend.result.result-prepare.annual-result') --}}
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert.js') }}"></script>
<script src="{{ asset('assets/libs/jquery-validate/jquery.validate.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@include('backend.result.reappear.reappear-result.components.search-marks-modal')
@include('backend.result.reappear.reappear-result.components.search-reappear-result')
@include('backend.result.reappear.reappear-result.components.fetch-reappear-result')
@include('backend.result.js.export-button')
@endsection
