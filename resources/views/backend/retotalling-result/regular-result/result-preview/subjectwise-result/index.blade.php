@extends('backend.partial.master')

@section('title', 'Retotalling Result')
@section('sub_title', 'Subjectwise Result Preview')

@section('backend-content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Subjectwise Result Card</h3>
                <div class="export-button">
                    <button type="button" id="export-excel-btn" class="btn btn-sm btn-success">Excel</button>
                    <button type="button" id="print-btn-a4" class="btn btn-sm btn-danger">Print PDF</button>
                </div>
                <div class="card-tools">
                    <a href="{{ route('retotal-result.regular.result-preview.overall.index') }}" target="_blank" class="btn btn-sm btn-success"><i data-feather="check-circle" style="width:14px; height:14px;"></i> End Sem Result Report</a>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#endSemOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Search Result
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="endSemMarksResult"></div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/libs/jquery-validate/jquery.validate.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@include('backend.retotalling-result.regular-result.result-preview.subjectwise-result.components.search-marks-modal')
@include('backend.retotalling-result.regular-result.result-preview.subjectwise-result.js.search-end-sem-result')
@include('backend.retotalling-result.regular-result.result-preview.subjectwise-result.js.fetch-end-sem-result')
@include('backend.result.js.export-button')

@endsection
