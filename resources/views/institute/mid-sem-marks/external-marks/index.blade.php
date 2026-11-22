@extends('institute.partial.master')
@section('title', 'Marks Prepare')
@section('sub_title', 'Mid Sem Marks Prepare')
@section('institute-content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="text-end">
                    <select id="lock_action" class="form-select form-select-sm w-auto d-inline">
                        <option value="">--Select Action--</option>
                        <option value="lock">Locked Marks</option>
                        <option value="unlock">Unlocked Marks</option>
                        <option value="forward">Forward to University</option>
                    </select>

                    <button type="button" class="btn btn-sm btn-success" id="apply_lock_action">
                        Apply
                    </button>
                </div>

                <div class="export-button">
                    <button type="button" id="export-excel-btn" class="btn btn-sm btn-success">Excel</button>
                    <button type="button" id="print-btn-a4" class="btn btn-sm btn-danger">Print PDF</button>
                </div>

                <div class="prepare-button">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Prepare/Search Marks
                    </button>

                    &nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#importMarksModal">
                        <i data-feather="upload" style="width:14px; height:14px;" class="me-1"></i>
                        Import Mid Sem Marks
                    </button>

                </div>
            </div>
        </div>
    </div>
    {{-- Fetch Mid-Sem Result --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <div id="resultContainer" style="display:none;">
                    <div class="table-responsive" id="resultRenderArea">
                        {{-- AJAX Result Render Here --}}
                    </div>
                </div>

                <div id="emptyState" class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486754.png" width="120" class="mb-3">
                    <h5>Select filters and click on Filter button</h5>
                    <p class="text-muted mb-0">Student marks result will appear here.</p>
                </div>

            </div>
        </div>
    </div>
</div>

@include('institute.mid-sem-marks.external-marks.components.search-marks-modal')
@include('institute.mid-sem-marks.external-marks.components.search-marks-js')
@include('institute.mid-sem-marks.external-marks.components.fetch-marks-js')
@include('institute.mid-sem-marks.external-marks.components.import-marks-modal')
@include('institute.mid-sem-marks.external-marks.components.export-sample-modal')
@include('institute.mid-sem-marks.external-marks.components.export-sample-marks-js')
@include('institute.mid-sem-marks.js.export-button')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

{{-- @section('scripts')
    @include('institute.mid-sem-marks.js.index')
    @include('institute.mid-sem-marks.js.export-button')
    @include('institute.mid-sem-marks.js.sample-download')
@endsection --}}

@endsection
