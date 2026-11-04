@extends('backend.partial.master')
@section('title', 'Retotalling Result')
@section('sub_title', 'Add/Update Subjects')
@section('backend-content')
<style>
    .error {
        color: #FC4B4B;
    }
</style>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Add/Update Subjects</h4>
                    <div class="buttondata">
                        <a href="{{ route('retotal-result.mapped-subjects.index') }}" class="btn btn-sm btn-secondary">
                            <i data-feather="arrow-left" style="width:14px; height:14px;"></i> Back</a>

                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="offcanvas" data-bs-target="#searchStudentOffcanvasTop">
                            <i data-feather="search" style="width:14px; height:14px;"></i> Search
                        </button>

                    </div>
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

@include('backend.retotalling-result.mapped-subjects.components.search-for-add-subject-modal')
@include('backend.retotalling-result.mapped-subjects.components.js.search-form-data')
@include('backend.retotalling-result.mapped-subjects.components.js.fetch-student')

@endsection
