@extends('backend.partial.master')
@section('title', 'Examination Form')
@section('sub_title', 'Examination Form List')
@section('backend-content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Examination Form</h3>

                <div class="export-button">
                    <button type="button" id="examform-print-btn" class="btn btn-sm btn-danger">Print PDF</button>
                </div>

                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#examinationOffcanvasTop">
                        <i data-feather="filter" style="width:14px; height:14px;"></i> Search Examination Form
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="examSheet">
                    <div class="text-center text-muted p-4">
                        Please select an exam and click Filter.
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert.js') }}"></script>

@include('backend.examination-form.components.search-modal-data-js')
@include('backend.examination-form.components.fetch-exam-form-js')
@include('backend.examination-form.components.search-modal')
@include('backend.result.js.export-button')

<script>

    /*
    |--------------------------------------------------------------------------
    | PRINT EXAMINATION FORMS
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '#examform-print-btn', function () {

        let printArea = document.getElementById('exam-form-print-area');

        if (!printArea) {
            alert('Examination form table not found.');
            return;
        }

        let rows = printArea.querySelectorAll('tbody tr');

        // Check actual data rows
        let hasData = false;

        rows.forEach(function (row) {

            if (
                row.querySelectorAll('td').length > 1 &&
                !row.innerText.includes('No paid examination forms found')
            ) {
                hasData = true;
            }

        });

        if (!hasData) {
            alert('Please load examination forms first.');
            return;
        }

        // Wait until browser finishes rendering
        setTimeout(function () {
            window.print();
        }, 300);

    });

</script>

@endsection
