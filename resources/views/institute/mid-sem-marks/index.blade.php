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
                {{-- <style>
                    #result-heading h5{
                        margin-block: 5px;
                    }
                </style> --}}
                {{-- <div class="table-responsive database-table">
                    <table class="table table-bordered yajra-datatable w-100">
                        <thead>
                            <tr id="result-heading">
                                <th colspan="7">
                                    <center>
                                        <h5>CONTROLLER OF EXAMINATIONS</h5>
                                        <h5>GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCE UNIVERSITY, LUDHIANA (INDIA)</h5>
                                        <h5 id="institute_name">
                                        {{
                                            auth('institute')->user()->account_type === 'institute'
                                                ? auth('institute')->user()->name
                                                : auth('institute')->user()->parentInstitute?->name
                                        }}
                                    </h5>
                                        <h5 id="exam_title"></h5>

                                        <h5>
                                            <span id="semester_text"></span> 
                                            - Session: <span id="session_text"></span>
                                        </h5>
                                    </center>

                                    <p><b>Subject Name :</b> <span id="subject_text"></span></p>
                                    <p><b>Maximum Marks :</b> <span id="max_marks_text"></span></p>
                                </th>
                            </tr>

                            <tr>
                                <th id="no-export">
                                    <input type="checkbox" id="select_all_students">
                                </th>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Roll No</th>
                                <th>Father Name</th>
                                <th>Max Marks</th>
                                <th>Obtained Marks</th>
                            </tr>
                        </thead>
                    </table>
                </div> --}}
            </div>
        </div>
    </div>
</div>



@include('institute.mid-sem-marks.modal.search-mid-sem')
@include('institute.mid-sem-marks.modal.import-marks')
@include('institute.mid-sem-marks.modal.export-sample-modal')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
 
@section('scripts')
    @include('institute.mid-sem-marks.js.index')
    @include('institute.mid-sem-marks.js.export-button')
    @include('institute.mid-sem-marks.js.sample-download')
@endsection

{{-- <script>
    window.instituteName = @json(
        auth('institute')->user()->account_type === 'institute'
            ? auth('institute')->user()->name
            : auth('institute')->user()->parentInstitute?->name
    ); --}}

@endsection
