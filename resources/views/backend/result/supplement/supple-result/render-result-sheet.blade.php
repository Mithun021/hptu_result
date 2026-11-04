<style>

.result-table-a4 {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
}

.result-table-a4 th,
.result-table-a4 td {
    border: 1px solid #ddd;
    padding: 2px;
    font-size: 12px;
    text-align: center;
    vertical-align: middle;
}

.result-table-a4 thead th {
    background: #f5f5f5;
    font-weight: 600;
}

.result-header h4 {
    margin: 2px 0;
}

.result-header p {
    margin: 4px 0;
    text-align: left;
}

.marks-input {
    width: 100%;
    height: 30px;
    text-align: center;
}

.absent-label {
    color: red;
    font-weight: bold;
}

.old-mark{
    font-weight:bold;
    margin-top:5px;
}

.adjust-inc-dec-btn::-webkit-outer-spin-button,
.adjust-inc-dec-btn::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Firefox */
.adjust-inc-dec-btn[type=number] {
    -moz-appearance: textfield;
}

.course-info{
    display:flex;
    justify-content:space-between;
    margin-bottom:6px;
    font-size:12px;
}

</style>

@php
    $marksTypeLabel = match ($request->marks_type) {
        'external'         => 'External',
        'theory_paper1'    => 'Theory Paper 1',
        'theory_paper2'    => 'Theory Paper 2',
        'practical_paper1' => 'Practical Paper 1',
        'practical_paper2' => 'Practical Paper 2',
        default             => ucfirst(str_replace('_', ' ', $request->marks_type)),
    };
@endphp

<form id="suppleResultForm">

    {{-- Hidden Data --}}

    <input type="hidden" name="exam_id" value="{{ $request->exam_id }}">
    <input type="hidden" name="course_type" value="{{ $request->course_type }}">
    <input type="hidden" name="course_id" value="{{ $request->course_id }}">
    <input type="hidden" name="scheme_id" value="{{ $request->scheme_id }}">
    <input type="hidden" name="branch_id" value="{{ $request->branch_id }}">
    <input type="hidden" name="semester_id" value="{{ $request->semester_id }}">
    <input type="hidden" name="annual_semester_id" value="{{ $request->annual_semester_id }}">
    <input type="hidden" name="batch_id" value="{{ $request->batch_id }}">
    <input type="hidden" name="institute_id" value="{{ $request->institute_id }}">
    <input type="hidden" name="subject_id" value="{{ $request->subject_id }}">
    <input type="hidden" name="marks_type" value="{{ $request->marks_type }}">

<table class="result-table-a4">

    <thead>

        <tr>

            <td colspan="6" class="result-header">

                <center>

                    <h4>CONTROLLER OF EXAMINATIONS</h4>

                    <h4>
                        GURU ANGAD DEV VETERINARY AND ANIMAL SCIENCE UNIVERSITY,
                        LUDHIANA (INDIA)
                    </h4>

                    <h4>{{ $exam->supple_result_sheet ?? $exam->exam_resultsheet_name }}</h4>

                </center>
                
                <p>
                    <b>
                        Marks Type : {{ $marksTypeLabel }}
                    </b>
                </p>

                <p>

                    Maximum Marks :

                    {{ $maxMarks }}

                </p>
                
                <div class="course-info">
                    <p>

                        <b>

                            {{ $subject->course->name }}

                            @if($subject->branch)

                                - {{ $subject->branch->name }}

                            @endif

                        </b>

                    </p>

                    <p>

                        Subject :

                        {{ $subject->name }}

                        ({{ $subject->subject_code }})

                    </p>
                </div>

            </td>

        </tr>

        <tr>

            <th width="40">

                <input type="checkbox" id="check-all" class="no-export">

            </th>

            <th>Admission Number / Roll No</th>

            <th>Student Name</th>
            <th>Father Name</th>

            <th>Max Marks</th>

            <th width="220">Obtained Marks</th>

        </tr>

    </thead>

    <tbody>

        @php
            $num = 1;
        @endphp

        @forelse($students as $row)

            @php
                $student = $row->student;
                $endSem = $row->end_sem_mark;
                $supple = $row->supple_mark;

                // dump($supple);
            @endphp


            <tr data-student-id="{{ $student->id }}">

                <td>

                    <input
                        type="checkbox"
                        class="student-check no-export"
                        value="{{ $student->id }}"
                    >

                    {{ $num++ }}

                </td>

                <td>

                    {{ $student->university_roll_no }}

                </td>

                <td>

                    {{ $student->student_name }}

                </td>
                
                <td>

                    {{ $student->father_name ?? '' }}

                </td>

                <td>

                    {{ $maxMarks }}

                </td>

                <td>

                    @if($supple && $supple->is_locked)

                        {{-- Locked Supplementary Result --}}

                        <div class="old-mark">

                            <strong>

                                {{ $supple->obtained_marks }}

                                @if($supple->exam_status == 'absent')

                                    <span class="absent-label">(Absent)</span>

                                @endif

                            </strong>

                        </div>

                    @else

                        {{-- Editable Supplementary Marks --}}

                        <input
                            type="number"
                            class="form-control marks-input adjust-inc-dec-btn"
                            min="0"
                            max="{{ $maxMarks }}"
                            value="{{ optional($supple)->obtained_marks }}"
                            @if(optional($supple)->exam_status=='absent') disabled @endif
                        >

                        <input
                            type="hidden"
                            class="grace-marks-input"
                            value="{{ optional($endSem)->grace_marks }}"
                        >

                        <div class="mt-1">

                            <label>

                                <input
                                    type="checkbox"
                                    class="mark-absent"
                                    @checked(optional($supple)->exam_status=='absent')
                                >

                                Absent

                            </label>

                        </div>

                    @endif


                    {{-- Original End Semester Marks (Reference Only) --}}

                    <div class="old-mark no-export">

                        <small>Previous :</small>

                        @if($endSem)

                            <span>

                                {{ $endSem->obtained_marks }}

                                @if($endSem->exam_status == 'absent')

                                    <span class="absent-label">(Absent)</span>

                                @endif

                            </span>

                        @else

                            -

                        @endif

                    </div>

                </td>

            </tr>

        @empty

        <tr>

            <td colspan="6">

                No Student Found.

            </td>

        </tr>

        @endforelse

        </tbody>
        
        <tfoot>

            <tr>
            
            <td colspan="2" class="align-left">
            
                Prepared by
            
                <br><br><br>
            
                Checked by
            
            </td>
            
            <td colspan="2">
                Controller of Examinations
            </td>
            
            <td colspan="2" class="align-right">
            
                Registrar, Guru Angad Dev Veterinary <br>
                and Animal Science University
            
            </td>
            
            </tr>
            
        </tfoot>

</table>

</form>


<script>

let isSaving = false;

function saveSuppleMark(row)
{
    if(isSaving){
        return;
    }

    isSaving = true;

    $.ajax({

        url:"{{ route('results.supplementary-result.save-mark') }}",

        type:"POST",

        data:{

            _token:"{{ csrf_token() }}",

            exam_id:$("input[name=exam_id]").val(),

            course_type:$("input[name=course_type]").val(),

            course_id:$("input[name=course_id]").val(),

            scheme_id:$("input[name=scheme_id]").val(),

            branch_id:$("input[name=branch_id]").val(),

            semester_id:$("input[name=semester_id]").val(),

            annual_semester_id:$("input[name=annual_semester_id]").val(),

            batch_id:$("input[name=batch_id]").val(),

            institute_id:$("input[name=institute_id]").val(),

            subject_id:$("input[name=subject_id]").val(),

            student_id:row.data("student-id"),

            marks_type:$("input[name=marks_type]").val(),

            max_marks:{{ $maxMarks }},

            obtained_marks:row.find(".marks-input").val(),

            grace_marks: row.find('input.grace-marks-input').val() || null,

            exam_status:row.find(".mark-absent").is(":checked")
                ? "absent"
                : "present"

        },

        success:function(res){

            row.find(".marks-input").data("old",row.find(".marks-input").val());

            Toastify({

                text:res.message,

                duration:2000,

                gravity:"top",

                position:"right",

                close:true,

                stopOnFocus:true,

                style:{
                    background:"#28a745"
                }

            }).showToast();

        },

        error:function(xhr){

            let message="Something went wrong.";

            if(xhr.responseJSON && xhr.responseJSON.message){
                message=xhr.responseJSON.message;
            }

            Toastify({

                text:message,

                duration:2500,

                gravity:"top",

                position:"right",

                close:true,

                stopOnFocus:true,

                style:{
                    background:"#dc3545"
                }

            }).showToast();

        },

        complete:function(){

            isSaving=false;

        }

    });

}


/*
|--------------------------------------------------------------------------
| Store Old Value
|--------------------------------------------------------------------------
*/

$(document).off("focus",".marks-input");

$(document).on("focus",".marks-input",function(){

    $(this).data("old",$(this).val());

});


/*
|--------------------------------------------------------------------------
| Blur Save
|--------------------------------------------------------------------------
*/

$(document).off("blur",".marks-input");

$(document).on("blur",".marks-input",function(){

    if($(this).prop("disabled")){
        return;
    }

    if($(this).data("old")==$(this).val()){
        return;
    }

    saveSuppleMark($(this).closest("tr"));

});


/*
|--------------------------------------------------------------------------
| Enter Save
|--------------------------------------------------------------------------
*/

$(document).off("keydown",".marks-input");

$(document).on("keydown",".marks-input",function(e){

    if(e.key==="Enter"){

        e.preventDefault();

        if($(this).prop("disabled")){
            return;
        }

        if($(this).data("old")==$(this).val()){
            return;
        }

        saveSuppleMark($(this).closest("tr"));

        // Update old value so blur won't save again
        $(this).data("old",$(this).val());

    }

});


/*
|--------------------------------------------------------------------------
| Absent Checkbox
|--------------------------------------------------------------------------
*/

$(document).off("change",".mark-absent");

$(document).on("change",".mark-absent",function(){

    let row=$(this).closest("tr");

    if($(this).is(":checked")){

        row.find(".marks-input")
            .val("")
            .prop("disabled",true);

    }else{

        row.find(".marks-input")
            .prop("disabled",false);

    }

    saveSuppleMark(row);

});


/*
|--------------------------------------------------------------------------
| Check All
|--------------------------------------------------------------------------
*/

$(document).off("change","#check-all");

$(document).on("change","#check-all",function(){

    $(".student-check").prop("checked",$(this).is(":checked"));

});

</script>
