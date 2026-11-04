@extends('backend.partial.master')

@section('title', 'Result')
@section('sub_title', 'Transcript')

@section('backend-content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Transcript</h3>
                <div class="export-button">
                    <button type="button" id="transcript-print-btn" class="btn btn-sm btn-danger">Print PDF</button>
                </div>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#transcriptOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Search Results
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="resultSheet"></div>
                {{-- @include('backend.result.transcript.result') --}}
            </div>
        </div>
    </div>
</div>


<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert.js') }}"></script>
<script src="{{ asset('assets/libs/jquery-validate/jquery.validate.min.js') }}"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@include('backend.result.transcript.transcript-modal')
@include('backend.result.js.search-transcript')
@include('backend.result.js.fetch-transcript')

@include('backend.result.js.export-button')

@endsection