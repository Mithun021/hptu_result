
<style>
#export-internal-offcanvas .offcanvas-body {
    min-height: 180px;
}
</style>

<div class="offcanvas offcanvas-top" tabindex="-1" id="export-internal-offcanvas" aria-labelledby="export-internal-offcanvas-label">

    <div class="offcanvas-header">
        <h5 id="export-internal-offcanvas-label">Export Result Sample</h5>

        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body card">

        <form action="{{ route('results.internal-marks.export') }}" method="POST">
            @csrf

            <div class="row">

                <div class="col-md-2 form-group mb-2 d-none">
                    <label for="export-internal-course-type">
                        Course Type<span class="text-danger">*</span>
                    </label>

                    <select name="course_type" id="export-internal-course-type" class="form-control">
                        <option value="">--Select--</option>
                        {{-- <option value="Annual Based">Annual Based</option> --}}
                        <option value="Semester Based" selected>Semester Based</option>
                    </select>

                    @error('course_type')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>


                <div class="col-md-4 form-group mb-2">
                    <label for="export-internal-mid-sem-exam-id">
                        Mid-Sem/Internal Exam Name<span class="text-danger">*</span>
                    </label>

                    <select name="mid_sem_exam_id" id="export-internal-mid-sem-exam-id" class="form-control">
                        <option value="">--Select--</option>
                    </select>
                </div>


                <div class="col-md-2 form-group mb-2">
                    <label for="export-internal-course-id">
                        Select Course<span class="text-danger">*</span>
                    </label>

                    <select name="course_id" id="export-internal-course-id" class="form-control">
                        <option value="">--Select--</option>

                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" data-course="{{ $course->course }}">
                                {{ $course->name }}
                            </option>
                        @endforeach

                    </select>
                </div>


                <div class="col-md-2 form-group mb-2">
                    <label for="export-internal-scheme-id">Select Scheme</label>

                    <select name="scheme_id" id="export-internal-scheme-id" class="form-control">
                        <option value="">--Select--</option>
                    </select>

                    @error('scheme_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>


                <div class="col-md-2 form-group mb-2">
                    <label for="export-internal-branch-id">
                        Select Branch<span class="text-danger">*</span>
                    </label>

                    <select name="branch_id" id="export-internal-branch-id" class="form-control">
                        <option value="">--Select--</option>
                    </select>
                </div>


                <div class="col-md-2 form-group mb-2" id="export-internal-semester-container" style="display: none;">

                    <label for="export-internal-semester-id">Select Semester</label>

                    <select name="semester_id" id="export-internal-semester-id" class="form-control">
                        <option value="">--Select--</option>
                    </select>

                </div>


                <div class="col-md-2 form-group mb-2" id="export-internal-annual-semester-container" style="display: none;">

                    <label for="export-internal-annual-semester-id">Select Annual Terms</label>

                    <select name="annual_semester_id" id="export-internal-annual-semester-id" class="form-control">
                        <option value="">--Select--</option>
                    </select>

                </div>


                <div class="col-md-2 form-group mb-2">
                    <label for="export-internal-batch-id">
                        Session / Batch <span class="text-danger">*</span>
                    </label>

                    <select name="batch_id" id="export-internal-batch-id" class="form-control">
                        <option value="">--Select--</option>
                    </select>
                </div>


                <div class="col-md-2 form-group mb-2">
                    <label for="export-internal-marks-type">
                        Select Marks Type<span class="text-danger">*</span>
                    </label>

                    <select name="marks_type" id="export-internal-marks-type" class="form-control">
                        <option value="">--Select--</option>
                    </select>
                </div>


                <div class="col-md-2 form-group mb-2" id="export-internal-subject-container">

                    <label for="export-internal-subject-id">
                        Select Subject<span class="text-danger">*</span>
                    </label>

                    <select name="subject_id" id="export-internal-subject-id" class="form-control">
                        <option value="">--Select--</option>
                    </select>

                </div>


                <div class="col-md-2 form-group mb-2" id="export-internal-institute-container">

                    <label for="export-internal-institute-id">
                        Select Institute<span class="text-danger">*</span>
                    </label>

                    <select name="institute_id" id="export-internal-institute-id" class="form-control">
                        <option value="">--Select--</option>
                    </select>

                </div>


                <div class="col-md-2 form-group mt-3 mb-2">
                    <button type="submit" id="export-internal-apply-filter" class="btn btn-primary">
                        Export/Download
                    </button>
                </div>

            </div>

        </form>

    </div>
</div>
