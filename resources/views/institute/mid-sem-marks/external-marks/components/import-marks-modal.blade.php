<div class="modal fade" id="importMarksModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="importSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="importMarksModalLabel">Import Student Marks</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('external.mid-sem-external-marks.import') }}" method="post" enctype="multipart/form-data">
                @csrf
            <div class="modal-body">
                <div class="alert alert-info" role="alert">
                    <h5 class="alert-heading">
                        <i class="mdi mdi-information-outline me-2"></i>
                        Student Marks CSV Import Instructions
                    </h5>
                    <ol class="mb-0">
                        <li><strong>Upload only CSV files.</strong> Other file types will not be accepted.</li>

                        <li>
                            <strong>Do not change the column headers.</strong> The first row must exactly match:
                            <code>
                                id, university_roll_no, student_name, internal_exam_name, course_type,
                                course_name, scheme_name, branch_name, semester_name,
                                annual_semester_name, batch_name, institute_name,
                                marks_type, subject_name, subject_code,
                                max_marks, obtained_marks, exam_status
                            </code>
                        </li>

                        <li>
                            <strong>Before uploading, confirm:</strong> course type, course, scheme, branch,
                            semester / annual semester, batch, and institute in the CSV
                            match the system records.
                        </li>

                        <li>
                            <strong>Enter only numeric values</strong> in the columns:
                            <code>max_marks</code> and <code>obtained_marks</code>.
                        </li>

                        <li>
                            <strong>Exam status</strong> should be entered properly
                            (e.g., present / absent) as per system format.
                        </li>

                        <li>
                            <strong>Download the sample CSV template</strong> before uploading
                            to ensure proper formatting.
                        </li>

                        <li>
                            <strong>Avoid extra spaces</strong> or special characters
                            to prevent import errors.
                        </li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Download Sample CSV</h5>
                                {{-- <a href="{{ asset('assets/sample/internal_marks_sample.csv') }}"
                                class="btn btn-primary"
                                download="internal_marks_sample.csv">
                                    <i class="mdi mdi-download me-1"></i> Download
                                </a> --}}
                                <button type="button" class="btn btn-sm btn-primary downloadSampleButton" data-bs-toggle="offcanvas" data-bs-target="#exportOffcanvasTop">
                                    <i class="mdi mdi-download me-1"></i> Download
                                </button>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group">
                                    {{-- <label for="">Import Subject</label> --}}
                                    <input type="file" name="file" class="form-control" accept=".csv" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Import Marks</button>
            </div>
            </form>
        </div>
    </div>
</div>
