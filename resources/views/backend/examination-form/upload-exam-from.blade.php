@extends('backend.partial.master')

@section('title', 'Examination Form')
@section('sub_title', 'Examination Form List')

@section('backend-content')

<div class="row">

    <div class="col-md-12">

        <div class="card">

            <div class="card-header">
                <h4 class="card-title">Upload Examination Forms CSV</h4>
            </div>

            <div class="card-body">

                {{-- Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}

                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                {{-- Error Message --}}
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}

                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                {{-- Validation Errors --}}
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Import Row Errors --}}
                @if(session('import_errors') && count(session('import_errors')) > 0)
                    <div class="alert alert-warning">
                        <h5>
                            Import Errors ({{ count(session('import_errors')) }})
                        </h5>

                        <hr>

                        <ul class="mb-0">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.examination-form.import') }}"
                    method="POST"
                    enctype="multipart/form-data">

                    @csrf

                    <div class="form-group">

                        <label for="csv_file">
                            Select CSV File <span class="text-danger">*</span>
                        </label>

                        <input type="file"
                            name="csv_file"
                            id="csv_file"
                            class="form-control"
                            accept=".csv,text/csv"
                            required>

                        <small class="text-muted">
                            Only CSV files are allowed. Maximum file size: 20 MB.
                        </small>

                    </div>

                    {{-- Upload & Back Buttons --}}
                    <div class="d-flex align-items-center flex-wrap mb-4 gap-2">

                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fa fa-upload"></i> Upload CSV
                        </button>

                        <a href="{{ route('admin.examination-form.index') }}"
                        class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>

                    </div>

                    {{-- Download Sample CSV Section --}}
                    <div class="p-3"
                        style="background: #f8f9fa; border: 1px dashed #28a745; border-radius: 6px;">

                        <div class="d-flex align-items-center justify-content-between flex-wrap">

                            <div class="mb-2 mb-md-0">
                                <h6 class="mb-1">
                                    <i class="fa fa-file-excel text-success"></i>
                                    Download Sample CSV
                                </h6>

                                <small class="text-muted">
                                    Download the sample file and fill in the examination form details using the required column format.
                                </small>
                            </div>

                            <a href="{{ asset('assets/sample/examination_form_sample.csv') }}"
                            class="btn btn-outline-success">
                                <i class="fa fa-download"></i> Download Sample
                            </a>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>


    {{-- CSV Format Information --}}
    <div class="col-md-12">

        <div class="card">

            <div class="card-header">
                <h4 class="card-title">CSV File Format</h4>
            </div>

            <div class="card-body">

                <div class="alert alert-info">
                    <strong>Important:</strong>
                    CSV headers must match the following names.
                    Column order can be different.
                    Do not include Form ID or Transaction ID; these will be generated automatically.
                </div>

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="35%">CSV Header</th>
                                <th>Description</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr>
                                <td>1</td>
                                <td>exam name</td>
                                <td>Exam master name</td>
                            </tr>

                            <tr>
                                <td>2</td>
                                <td>student university roll no</td>
                                <td>Student university roll number</td>
                            </tr>

                            <tr>
                                <td>3</td>
                                <td>course_name</td>
                                <td>Course name</td>
                            </tr>

                            <tr>
                                <td>4</td>
                                <td>branch_name</td>
                                <td>Branch name</td>
                            </tr>

                            <tr>
                                <td>5</td>
                                <td>scheme</td>
                                <td>Scheme name</td>
                            </tr>

                            <tr>
                                <td>6</td>
                                <td>semester</td>
                                <td>Semester / Annual Semester name</td>
                            </tr>

                            <tr>
                                <td>7</td>
                                <td>batch</td>
                                <td>Batch name</td>
                            </tr>

                            <tr>
                                <td>8</td>
                                <td>student_institute</td>
                                <td>Student institute name</td>
                            </tr>

                            <tr>
                                <td>9</td>
                                <td>exam_center_name</td>
                                <td>Examination center name</td>
                            </tr>

                            <tr>
                                <td>10</td>
                                <td>exam_type</td>
                                <td>Regular / Reappear / Special Chance</td>
                            </tr>

                            <tr>
                                <td>11</td>
                                <td>exam_fee_amount</td>
                                <td>Examination fee amount</td>
                            </tr>

                            <tr>
                                <td>12</td>
                                <td>late fee</td>
                                <td>Late fee amount</td>
                            </tr>

                            <tr>
                                <td>13</td>
                                <td>total fee</td>
                                <td>Total examination fee</td>
                            </tr>

                            <tr>
                                <td>14</td>
                                <td>last_appearning_session</td>
                                <td>Last appearing session</td>
                            </tr>

                            <tr>
                                <td>15</td>
                                <td>last_appearing_year</td>
                                <td>Last appearing year</td>
                            </tr>

                            <tr>
                                <td>16</td>
                                <td>concatenated_subject_codes</td>
                                <td>Comma-separated subject codes</td>
                            </tr>

                        </tbody>

                    </table>

                </div>

                <hr>

                <h5>Example Subject Codes Format</h5>

                <p>
                    Multiple subject codes must be enclosed in double quotes
                    inside the CSV cell.
                </p>

                <code>"SUB-101,SUB-102,SUB-103"</code>

            </div>

        </div>

    </div>

</div>

@endsection
