@extends('institute.partial.master')
@section('title','Edit Assign Subjects')
@section('sub_title','Edit Assign Subjects To Teacher')

@section('institute-content')

<div class="card">
    <div class="card-header">
        <h4>Edit Assigned Subjects</h4>
    </div>

    <form action="{{ route('update.assign.subject',$teacher->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div class="row">

                {{-- Course Type --}}
                <div class="col-md-4 mb-2">
                    <label>Course Type</label>
                    <select name="course_type" id="course_type" class="form-control">
                        <option value="Annual Based" {{ $selectedCourseType=='Annual Based'?'selected':'' }}>
                            Annual Based
                        </option>
                        <option value="Semester Based" {{ $selectedCourseType=='Semester Based'?'selected':'' }}>
                            Semester Based
                        </option>
                    </select>
                </div>

                {{-- Course --}}
                <div class="col-md-4 mb-2">
                    <label>Course</label>
                    <select name="course_id" id="course_id" class="form-control">
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}"
                                {{ $selectedCourse==$course->id?'selected':'' }}>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Branch --}}
                <div class="col-md-4 mb-2">
                    <label>Branch</label>
                    <select name="branch_id" id="branch_id" class="form-control"></select>
                </div>

                {{-- Semester --}}
                <div class="col-md-4 mb-2" id="semester_container">
                    <label>Semester</label>
                    <select name="semester_id" id="semester_id" class="form-control"></select>
                </div>

                {{-- Annual Semester --}}
                <div class="col-md-4 mb-2" id="annual_semester_container">
                    <label>Annual Terms</label>
                    <select name="annual_semester_id" id="annual_semester_id" class="form-control"></select>
                </div>

                {{-- Teacher (Readonly) --}}
                <div class="col-md-4 mb-2">
                    <label>Teacher</label>
                    <select class="form-control" disabled>
                        @foreach($teachers as $t)
                            @if($teacher->id == $t->id)
                                <option selected>{{ $t->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                </div>

                {{-- Subjects --}}
                <div class="col-md-12">
                    <label>Subjects</label>
                    <div id="subject_checkbox_container"
                         class="border p-3 rounded"
                         style="max-height:250px;overflow-y:auto;">
                        Loading...
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer">
            <button class="btn btn-primary">Update Subjects</button>
            <a href="{{ route('assign.subject') }}" class="btn btn-secondary">Back</a>
        </div>

    </form>
</div>

<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>

<script>
let selectedBranch = "{{ $selectedBranch }}";
let selectedSemester = "{{ $selectedSemester }}";
let selectedAnnual = "{{ $selectedAnnual }}";
let assignedSubjects = @json($assignedSubjects->pluck('id'));

function loadCourseData(courseId){

    $.get("{{ url('institute/branches') }}/"+courseId,function(branches){
        $('#branch_id').html('');
        $.each(branches,function(_,b){
            $('#branch_id').append(
                `<option value="${b.id}" ${selectedBranch==b.id?'selected':''}>${b.name}</option>`
            );
        });
    });

    $.get("{{ url('courses') }}/"+courseId+"/details",function(res){

        if($('#course_type').val()==='Semester Based'){
            $('#semester_container').show();
            $('#annual_semester_container').hide();
            $('#semester_id').html('');

            $.each(res.semesters,function(_,s){
                $('#semester_id').append(
                    `<option value="${s.id}" ${selectedSemester==s.id?'selected':''}>${s.name}</option>`
                );
            });
        }else{
            $('#annual_semester_container').show();
            $('#semester_container').hide();
            $('#annual_semester_id').html('');

            $.each(res.annual_semesters,function(_,a){
                $('#annual_semester_id').append(
                    `<option value="${a.id}" ${selectedAnnual==a.id?'selected':''}>${a.name}</option>`
                );
            });
        }

        loadSubjects();
    });
}

function loadSubjects(){
    $.get("{{ url('subjects/filter') }}",{
        course_type:$('#course_type').val(),
        course_id:$('#course_id').val(),
        branch_id:$('#branch_id').val(),
        semester_id:$('#semester_id').val(),
        annual_semester_id:$('#annual_semester_id').val()
    },function(subjects){

        let html='';

        $.each(subjects,function(_,s){
            html+=`
                <div class="form-check mb-1">
                    <input type="checkbox"
                        class="form-check-input"
                        name="subject_ids[]"
                        value="${s.id}"
                        ${assignedSubjects.includes(s.id)?'checked':''}>
                    <label>${s.name}</label>
                </div>`;
        });

        $('#subject_checkbox_container').html(html);
    });
}

$('#course_id').change(function(){ loadCourseData($(this).val()); });
$('#course_type').change(function(){ loadCourseData($('#course_id').val()); });
$('#branch_id,#semester_id,#annual_semester_id').change(loadSubjects);

$(document).ready(function(){
    loadCourseData($('#course_id').val());
});
</script>

@endsection 