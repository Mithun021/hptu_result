@extends('backend.partial.master')
@section('title', 'Supplementary Result')
@section('sub_title', 'Assign Subjects')
@section('backend-content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Mapped Student Supple/Compartment Subjects</h3>
                <div class="export-button">
                    <button type="button" id="print-btn-a4" class="btn btn-sm btn-danger">Print PDF</button>
                </div>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#suppleSubjectOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Search
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
<script src="{{ asset('assets/libs/jquery-validate/jquery.validate.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

@include('backend.result.supplement.assign-subject-modal')
@include('backend.result.supplement.assign-subject-js')
@include('backend.result.supplement.fetch-assign-subject')
@include('backend.result.js.export-button')

@endsection
