 @extends('backend.partial.master')

@section('title', 'Result Prepare')
@section('sub_title', 'End Semester Front Sheet')

@section('backend-content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">End Semester Front Sheet</h3>

                <div class="export-button">
                    <button type="button" id="export-excel-btn" class="btn btn-sm btn-success">Excel</button>
                    <button type="button" id="print-btn-a4" class="btn btn-sm btn-danger">Print PDF</button>
                </div>

                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#senSemCourseOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Search End Semester Result
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="resultSheet"></div>

                {{-- @include('backend.result.front-sheet.nc-course.result') --}}

            </div>
        </div>
    </div>
</div>


<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert.js') }}"></script>

@include('backend.result.front-sheet.components.search-end-sem-front-sheet-js')
@include('backend.result.front-sheet.components.fetch-end-sem-front-sheet-js')
@include('backend.result.front-sheet.components.end-sem-front-sheet-modal')
@include('backend.result.js.export-button')

@endsection
