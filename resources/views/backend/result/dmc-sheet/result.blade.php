<style>
    /* ================================
       DMC SHEET
    ================================= */
    .compartment-sheet {
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    /* ================================
       WATERMARK
    ================================= */
    .compartment-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 500px;
        height: 500px;
        transform: translate(-50%, -50%);
        background-image: url("{{ asset('assets/images/watermark.png') }}");
        background-repeat: no-repeat;
        background-position: center center;
        background-size: contain;
        opacity: 0.08;
        pointer-events: none;
        z-index: 0;
    }

    .compartment-watermark img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    /* ================================
       TABLE
    ================================= */
    table.compartment-table {
        position: relative;
        width: 100%;
        border: 0;
        border-collapse: collapse;
        font-size: 14px;
        z-index: 1;
    }

    table.compartment-table thead,
    table.compartment-table tbody,
    table.compartment-table tfoot {
        position: relative;
        z-index: 2;
    }

    /* ================================
       HEADER
    ================================= */
    table.compartment-table thead tr td,
    table.compartment-table thead tr th {
        font-weight: bold;
        border: 1px solid #000 !important;
        border-collapse: collapse;
        font-size: 12px;
        text-align: center;
        vertical-align: middle !important;
    }

    /* ================================
       NO BORDER HEADER ROW
    ================================= */
    table.compartment-table thead tr.no-border,
    table.compartment-table thead tr.no-border td,
    table.compartment-table thead tr.no-border th,
    table.compartment-table .no-border {
        border: none !important;
    }

    /* ================================
       HEADER INFORMATION
    ================================= */
    /*table.compartment-table thead .header_info {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }

    table.compartment-table thead .header_info .personal-detail {
        width: 50%;
        font-weight: inherit;
        text-align: left;
        margin-left : 20px;
    }*/
    
    
    /* ================================
   STUDENT PERSONAL DETAILS
================================= */
table.compartment-table thead .header_info {
    display: grid;
    grid-template-columns: 55% 45%;
    width: calc(100% - 40px);
    margin: 8px 20px 0 28px;
    padding: 0;
    align-items: center;
    column-gap: 0;
    row-gap: 8px;
    box-sizing: border-box;
}

table.compartment-table thead .header_info .personal-detail {
    display: grid;
    grid-template-columns: 110px minmax(0, 1fr);
    align-items: center;
    width: 100%;
    margin: 0;
    padding: 0;
    font-size: 14px;
    font-weight: 400;
    line-height: 1.4;
    text-align: left;
    color: #52647a;
    box-sizing: border-box;
}

table.compartment-table thead .header_info .personal-detail span {
    display: block;
    width: auto;
    white-space: nowrap;
    font-weight: 400;
    color: #52647a;
}

table.compartment-table thead .header_info .personal-detail b {
    display: block;
    min-width: 0;
    font-size: 14px;
    font-weight: 600;
    color: #52647a;
    overflow-wrap: anywhere;
}

/* Print layout */
@media print {
    table.compartment-table thead .header_info {
        grid-template-columns: 55% 45%;
        width: calc(100% - 40px);
        margin: 8px 20px 0 28px;
        column-gap: 0;
        row-gap: 8px;
    }

    table.compartment-table thead .header_info .personal-detail {
        grid-template-columns: 110px minmax(0, 1fr);
        font-size: 14px;
    }

    table.compartment-table thead .header_info .personal-detail b {
        font-size: 14px;
    }
}


    /* ================================
       BODY
    ================================= */
    table.compartment-table tbody {
        border: 1px solid #000 !important;
    }

    table.compartment-table tbody tr td {
        border: 1px solid #000 !important;
        text-align: center;
        vertical-align: middle !important;
        font-size: 12px;
    }

    /* ================================
       FOOTER
    ================================= */
    table.compartment-table tfoot tr td {
        border: none !important;
        vertical-align: middle !important;
    }

    table.compartment-table tfoot tr.bottom-signature td {
        padding: 60px 30px 0px 30px;
    }

    /* ================================
       TEXT ALIGNMENT
    ================================= */
    .text-left {
        text-align: left !important;
    }

    table.compartment-table tfoot tr.bottom-signature td.text-right {
        text-align: right !important;
    }

    /* ================================
       MARKS HEADER
    ================================= */
    tr.top-bottom-border {
        border: 1px solid #000 !important;
        text-align: center;
        vertical-align: middle !important;
    }

    tr.top-bottom-border td,
    tr.top-bottom-border th {
        border: 1px solid #000 !important;
        text-align: center;
        vertical-align: middle !important;
    }

    /* ================================
       FLEX
    ================================= */
    .flex-between {
        display: flex;
        justify-content: space-between;
    }

    /* ================================
       COLLEGE LOGO
    ================================= */
    .clg-logo {
        width: 100px;
        height: auto;
        position: relative;
        top: 0;
        left: 0;
        margin: 10px auto;
    }

    /* ================================
       MARGIN
    ================================= */
    .margin-b25 {
        margin-bottom: 25px;
    }

    /* ================================
       MULTIPLE DMC
    ================================= */
    .dmc-page {
        page-break-after: always;
        break-after: page;
    }

    .dmc-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }
    .font-40{
        font-size : 22px;
    }
    
    .flex-between p span u {
        text-underline-offset: 5px;
    }
</style>


<div id="dmc-print-container">
    
    @php
        use App\Models\Student;
    @endphp

    @php

        /*
        |--------------------------------------------------------------------------
        | ANNUAL YEAR NUMBER FUNCTION
        |--------------------------------------------------------------------------
        |
        | 1st Year  => 1
        | 2nd Year  => 2
        | 3rd Year  => 3
        | 4th Year  => 4
        |
        */

        $getAnnualYearNumber = function ($name) {

            $name = strtolower(trim((string) $name));

            if (
                str_contains($name, '1st') ||
                str_contains($name, 'first')
            ) {
                return 1;
            }

            if (
                str_contains($name, '2nd') ||
                str_contains($name, 'second')
            ) {
                return 2;
            }

            if (
                str_contains($name, '3rd') ||
                str_contains($name, 'third')
            ) {
                return 3;
            }

            if (
                str_contains($name, '4th') ||
                str_contains($name, 'fourth')
            ) {
                return 4;
            }

            if (
                preg_match('/\b([1-4])\b/', $name, $matches)
            ) {
                return (int) $matches[1];
            }

            return '';
        };


        /*
        |--------------------------------------------------------------------------
        | MARKS TYPE FINDER
        |--------------------------------------------------------------------------
        */

        $getMarksGroup = function (
            $studentMarks,
            $subjectId,
            $marksType
        ) {

            return optional(
                optional($studentMarks[$subjectId] ?? collect())
                    ->get($marksType)
            )->first();

        };


        /*
        |--------------------------------------------------------------------------
        | NUMBER FORMAT
        |--------------------------------------------------------------------------
        */

        $formatMark = function ($value) {

            if ($value === null || $value === '') {
                return '-';
            }

            if (!is_numeric($value)) {
                return $value;
            }

            return number_format((float) $value, 2);
        };
        
        $num = 1;

    @endphp


    {{-- ================================================================
         STUDENTS
         ================================================================ --}}

    @forelse($students as $studentIndex => $student)

        @php

            /*
            |--------------------------------------------------------------------------
            | STUDENT ID
            |--------------------------------------------------------------------------
            */

            $studentId = $student->id;


            /*
            |--------------------------------------------------------------------------
            | STUDENT SUBJECTS
            |--------------------------------------------------------------------------
            |
            | Student ke sirf enrolled subjects show honge.
            |
            */

            $enrolledStudentSubjects = $studentSubjects
                ->get($studentId, collect());


            /*
            |--------------------------------------------------------------------------
            | ENROLLED SUBJECT IDS
            |--------------------------------------------------------------------------
            */

            $enrolledSubjectIds = $enrolledStudentSubjects
                ->pluck('subject_id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->toArray();


            /*
            |--------------------------------------------------------------------------
            | FILTER SUBJECTS
            |--------------------------------------------------------------------------
            |
            | Master subjects me se sirf student ke enrolled subjects.
            |
            */

            $studentSubjectsList = $subjects
                ->filter(function ($subject) use ($enrolledSubjectIds) {

                    return in_array(
                        (int) $subject->id,
                        $enrolledSubjectIds,
                        true
                    );
                })
                ->values();


            /*
            |--------------------------------------------------------------------------
            | STATE
            |--------------------------------------------------------------------------
            */

            $cityName = optional($institute)->city ?? '';

            $cityFirstLetter = strtoupper(
                substr(
                    trim((string) $cityName),
                    0,
                    1
                )
            );


            /*
            |--------------------------------------------------------------------------
            | BATCH
            |--------------------------------------------------------------------------
            */

            $batchName = optional($batch)->name ?? '-';


            /*
            |--------------------------------------------------------------------------
            | INSTITUTE SORT NAME
            |--------------------------------------------------------------------------
            */

            $instituteSortName = optional($institute)->sort_name
                ?: 'NA';


            /*
            |--------------------------------------------------------------------------
            | ANNUAL YEAR
            |--------------------------------------------------------------------------
            */

            $annualYearNumber = $getAnnualYearNumber(
                optional($annualSemester)->name
            );


            /*
            |--------------------------------------------------------------------------
            | SR NO
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | L/2025/CV/1/01
            |
            */

            // $serialNumber = str_pad(
            //     $studentIndex + 1,
            //     2,
            //     '0',
            //     STR_PAD_LEFT
            // );
            
            
            
            /*
            |--------------------------------------------------------------------------
            | SR NO
            |--------------------------------------------------------------------------
            | All students: serial number according to student order.
            | Single student search: calculate the same position from full student list.
            |--------------------------------------------------------------------------
            */
            
            $serialNumber = str_pad(
                $studentRollNumber !== ''
                    ? (function () use (
                        $courseType,
                        $courseId,
                        $branchId,
                        $batchId,
                        $schemeId,
                        $instituteId,
                        $student
                    ) {
            
                        $studentsQuery = Student::where('course_type', $courseType)
                            ->where('course_id', $courseId)
                            ->where('branch_id', $branchId)
                            ->where('batch_id', $batchId)
            
                            ->when($schemeId, function ($q) use ($schemeId) {
                                $q->where('scheme_id', $schemeId);
                            })
            
                            ->when($instituteId, function ($q) use ($instituteId) {
                                $q->where('institute_id', $instituteId);
                            });
            
                        // Count students up to the current student's position
                        return (clone $studentsQuery)
                            ->where(function ($query) use ($student) {
            
                                $query->whereRaw("
                                    CAST(
                                        SUBSTRING_INDEX(
                                            SUBSTRING_INDEX(university_roll_no, '-', 2),
                                            '-', -1
                                        ) AS UNSIGNED
                                    ) < ?
                                ", [
                                    (int) explode('-', $student->university_roll_no)[1]
                                ])
            
                                ->orWhere(function ($q) use ($student) {
            
                                    $q->whereRaw("
                                        CAST(
                                            SUBSTRING_INDEX(
                                                SUBSTRING_INDEX(university_roll_no, '-', 2),
                                                '-', -1
                                            ) AS UNSIGNED
                                        ) = ?
                                    ", [
                                        (int) explode('-', $student->university_roll_no)[1]
                                    ])
            
                                    ->whereRaw("
                                        CAST(
                                            SUBSTRING_INDEX(
                                                SUBSTRING_INDEX(university_roll_no, '-', 4),
                                                '-', -1
                                            ) AS UNSIGNED
                                        ) <= ?
                                    ", [
                                        (int) explode('-', $student->university_roll_no)[3]
                                    ]);
                                });
                            })
                            ->count();
            
                    })()
                    : $studentIndex + 1,
            
                2,
                '0',
                STR_PAD_LEFT
            );



            $srNo =
                $cityFirstLetter
                . '/'
                . $batchName
                . '/'
                . strtoupper($instituteSortName)
                . '/'
                . $annualYearNumber
                . '/'
                . $serialNumber;


            /*
            |--------------------------------------------------------------------------
            | ADMISSION NUMBER
            |--------------------------------------------------------------------------
            |
            | DMC Admission No. = university_roll_no
            |
            */

            $admissionNo =
                $student->university_roll_no
                ?? '-';


            /*
            |--------------------------------------------------------------------------
            | RESULT VARIABLES
            |--------------------------------------------------------------------------
            */

            $failedSubjects = collect();

            $totalCreditHours = 0;

            $totalCreditPoints = 0;


            /*
            |--------------------------------------------------------------------------
            | SUBJECT RESULT DATA
            |--------------------------------------------------------------------------
            */

            $dmcSubjects = [];


            /*
            |--------------------------------------------------------------------------
            | LOOP SUBJECTS
            |--------------------------------------------------------------------------
            */

            foreach ($studentSubjectsList as $subject) {

                $subjectId = $subject->id;


                /*
                |--------------------------------------------------------------------------
                | IS NC
                |--------------------------------------------------------------------------
                */

                $isNc = (bool) ($subject->is_nc ?? false);


                /*
                |--------------------------------------------------------------------------
                | CREDIT DISPLAY
                |--------------------------------------------------------------------------
                |
                | Annual Based:
                |
                | DISPLAY  = theory_credits + practical_credits
                | CALCULATION = credits
                |
                | Same distinction as Annual Based result logic.
                |
                */

                $theoryCredit = (float) ($subject->theory_credits ?? 0);

                $practicalCredit = (float) ($subject->practical_credits ?? 0);

                $creditDisplay =
                    $theoryCredit
                    . '+'
                    . $practicalCredit;


                /*
                |--------------------------------------------------------------------------
                | MARKS
                |--------------------------------------------------------------------------
                */

                $studentMarks = $midSemMarks
                    ->get($studentId, collect())
                    ->get($subjectId, collect());


                $studentEndMarks = $endSemMarks
                    ->get($studentId, collect())
                    ->get($subjectId, collect());


                $studentAdjustments = $academicAdjustments
                    ->get($studentId, collect())
                    ->get($subjectId, collect());


                /*
                |--------------------------------------------------------------------------
                | NC SUBJECT
                |--------------------------------------------------------------------------
                |
                | NC grade directly from EndSemMark->grade
                |
                */

                if ($isNc) {

                    $ncEndSemMark = null;


                    /*
                    | Search any marks_type for this NC subject
                    */

                    foreach (
                        $studentEndMarks as $marksTypeRows
                    ) {

                        $ncEndSemMark =
                            optional($marksTypeRows)->first();

                        if ($ncEndSemMark) {
                            break;
                        }
                    }


                    $ncGrade =
                        optional($ncEndSemMark)->grade
                        ?? '';


                    $dmcSubjects[] = [
                        'subject' => $subject,
                        'is_nc' => true,
                        'credit_display' => $creditDisplay,
                        'nc_grade' => $ncGrade,
                        'result' => strtoupper($ncGrade) === 'U'
                            ? 'FAIL'
                            : 'PASS',
                    ];


                    /*
                    | NC is excluded from credit/GPA calculation
                    */

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | INTERNAL 1
                |--------------------------------------------------------------------------
                */

                $internal1Mark = null;

                $internal2Mark = null;

                $internal3Mark = null;


                /*
                |--------------------------------------------------------------------------
                | FIND INTERNAL MARKS
                |--------------------------------------------------------------------------
                */

                foreach ($studentMarks as $marksType => $rows) {

                    $type = strtolower(
                        trim((string) $marksType)
                    );


                    $mark = optional($rows)->first();


                    if (!$mark) {
                        continue;
                    }


                    if (
                        str_contains($type, 'internal 1') ||
                        str_contains($type, 'internal_1') ||
                        str_contains($type, 'internal1')
                    ) {

                        $internal1Mark = $mark;

                    } elseif (
                        str_contains($type, 'internal 2') ||
                        str_contains($type, 'internal_2') ||
                        str_contains($type, 'internal2')
                    ) {

                        $internal2Mark = $mark;

                    } elseif (
                        str_contains($type, 'internal 3') ||
                        str_contains($type, 'internal_3') ||
                        str_contains($type, 'internal3')
                    ) {

                        $internal3Mark = $mark;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | INTERNAL FINAL = OBTAINED + GRACE
                |--------------------------------------------------------------------------
                */

                $internalValues = [];


                if ($internal1Mark) {

                    $obtained =
                        (float) (
                            $internal1Mark->obtained_marks
                            ?? 0
                        );

                    $grace =
                        (float) (
                            $internal1Mark->grace_marks
                            ?? 0
                        );

                    $internalValues[] =
                        $obtained + $grace;
                }


                if ($internal2Mark) {

                    $obtained =
                        (float) (
                            $internal2Mark->obtained_marks
                            ?? 0
                        );

                    $grace =
                        (float) (
                            $internal2Mark->grace_marks
                            ?? 0
                        );

                    $internalValues[] =
                        $obtained + $grace;
                }


                if ($internal3Mark) {

                    $obtained =
                        (float) (
                            $internal3Mark->obtained_marks
                            ?? 0
                        );

                    $grace =
                        (float) (
                            $internal3Mark->grace_marks
                            ?? 0
                        );

                    $internalValues[] =
                        $obtained + $grace;
                }


                /*
                |--------------------------------------------------------------------------
                | BEST TWO INTERNALS
                |--------------------------------------------------------------------------
                */

                rsort($internalValues);


                $internal1Final =
                    $internalValues[0] ?? 0;


                $internal2Final =
                    $internalValues[1] ?? 0;


                /*
                |--------------------------------------------------------------------------
                | BEST TWO INTERNAL TOTAL
                |--------------------------------------------------------------------------
                */

                $internalTotal =
                    $internal1Final
                    + $internal2Final;


                /*
                |--------------------------------------------------------------------------
                | THEORY / PRACTICAL
                |--------------------------------------------------------------------------
                */

                $theory1 = 0;

                $theory2 = 0;

                $practical1 = 0;

                $practical2 = 0;


                /*
                |--------------------------------------------------------------------------
                | GET END SEM MARKS
                |--------------------------------------------------------------------------
                */

                foreach (
                    $studentEndMarks as $marksType => $rows
                ) {

                    $type = strtolower(
                        trim((string) $marksType)
                    );


                    $mark = optional($rows)->first();


                    if (!$mark) {
                        continue;
                    }


                    /*
                    | Academic Adjustment
                    */

                    $adjustment = 0;


                    if (
                        isset(
                            $studentAdjustments[$marksType]
                        )
                    ) {

                        $adjustmentRow =
                            $studentAdjustments[$marksType]
                                ->first();

                        $adjustment =
                            (float) (
                                $adjustmentRow->adjustment_marks
                                ?? 0
                            );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | ALSO CHECK ADJUSTMENT BY TYPE
                    |--------------------------------------------------------------------------
                    */

                    if ($adjustment == 0) {

                        foreach (
                            $studentAdjustments as $adjustmentType => $adjustmentRows
                        ) {

                            if (
                                strtolower(
                                    trim((string) $adjustmentType)
                                )
                                === $type
                            ) {

                                $adjustmentRow =
                                    $adjustmentRows->first();

                                $adjustment =
                                    (float) (
                                        $adjustmentRow->adjustment_marks
                                        ?? 0
                                    );

                                break;
                            }
                        }
                    }


                    $obtained =
                        (float) (
                            $mark->obtained_marks
                            ?? 0
                        );


                    $finalMark =
                        $obtained + $adjustment;


                    /*
                    |--------------------------------------------------------------------------
                    | THEORY PAPER 1
                    |--------------------------------------------------------------------------
                    */

                    if (
                        str_contains($type, 'theory_paper1') ||
                        str_contains($type, 'theory paper 1')
                    ) {

                        $theory1 = $finalMark;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | THEORY PAPER 2
                    |--------------------------------------------------------------------------
                    */

                    elseif (
                        str_contains($type, 'theory_paper2') ||
                        str_contains($type, 'theory paper 2')
                    ) {

                        $theory2 = $finalMark;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PRACTICAL PAPER 1
                    |--------------------------------------------------------------------------
                    */

                    elseif (
                        str_contains($type, 'practical_paper1') ||
                        str_contains($type, 'practical paper 1')
                    ) {

                        $practical1 = $finalMark;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PRACTICAL PAPER 2
                    |--------------------------------------------------------------------------
                    */

                    elseif (
                        str_contains($type, 'practical_paper2') ||
                        str_contains($type, 'practical paper 2')
                    ) {

                        $practical2 = $finalMark;

                    }
                }


                /*
                |--------------------------------------------------------------------------
                | TOTALS
                |--------------------------------------------------------------------------
                */

                $theoryTotal =
                    $internalTotal
                    + $theory1
                    + $theory2;


                $practicalTotal =
                    $practical1
                    + $practical2;


                $total =
                    $theoryTotal
                    + $practicalTotal;


                /*
                |--------------------------------------------------------------------------
                | MAXIMUM MARKS
                |--------------------------------------------------------------------------
                |
                | Annual Based:
                |
                | Gross Theory =
                | Best 2 Internal + Theory Paper 1 + Theory Paper 2
                |
                | Practical =
                | Practical Paper 1 + Practical Paper 2
                |
                */

                $internalMax1 =
                    $subject->internal1
                    ?? $subject->internal_1
                    ?? 10;

                $internalMax2 =
                    $subject->internal2
                    ?? $subject->internal_2
                    ?? 10;

                $internalMax3 =
                    $subject->internal3
                    ?? $subject->internal_3
                    ?? 10;


                $internalMaximums = [
                    (float) $internalMax1,
                    (float) $internalMax2,
                    (float) $internalMax3,
                ];


                rsort($internalMaximums);


                $bestInternalMaximum =
                    ($internalMaximums[0] ?? 10)
                    + ($internalMaximums[1] ?? 10);
                    


                /*
                |--------------------------------------------------------------------------
                | THEORY MAX
                |--------------------------------------------------------------------------
                */

                $theoryPaper1Max =
                    $subject->paper1
                    ?? $subject->theory_paper1
                    ?? 40;

                $theoryPaper2Max =
                    $subject->paper2
                    ?? $subject->theory_paper2
                    ?? 0;


                /*
                |--------------------------------------------------------------------------
                | PRACTICAL MAX
                |--------------------------------------------------------------------------
                */

                $practicalPaper1Max =
                    $subject->practical_paper1
                    ?? $subject->practical1
                    ?? 40;

                $practicalPaper2Max =
                    $subject->practical_paper2
                    ?? $subject->practical2
                    ?? 0;
                    
                    
                $bestInternalMaximum1 =
                    $internalMaximums[0] ?? 10;
                
                $bestInternalMaximum2 =
                    $internalMaximums[1] ?? 10;
                
                $dmcTheoryMaximum =
                    (float) $theoryPaper1Max
                    + (float) $theoryPaper2Max;
                
                $dmcPracticalMaximum =
                    (float) $practicalPaper1Max
                    + (float) $practicalPaper2Max;


                /*
                |--------------------------------------------------------------------------
                | GROSS THEORY MAX
                |--------------------------------------------------------------------------
                */

                $grossTheoryMax =
                    $bestInternalMaximum
                    + (float) $theoryPaper1Max
                    + (float) $theoryPaper2Max;


                /*
                |--------------------------------------------------------------------------
                | PRACTICAL MAX
                |--------------------------------------------------------------------------
                */

                $practicalTotalMax =
                    (float) $practicalPaper1Max
                    + (float) $practicalPaper2Max;


                /*
                |--------------------------------------------------------------------------
                | REQUIRED MARKS
                |--------------------------------------------------------------------------
                */

                $requiredGrossTheory =
                    $grossTheoryMax
                    * $overallPassPercent
                    / 100;


                $requiredPractical =
                    $practicalTotalMax
                    * $overallPassPercent
                    / 100;


                /*
                |--------------------------------------------------------------------------
                | PASS / FAIL
                |--------------------------------------------------------------------------
                */

                $isPassed =
                    $theoryTotal >= $requiredGrossTheory
                    &&
                    $practicalTotal >= $requiredPractical;


                /*
                |--------------------------------------------------------------------------
                | GRADE POINT / CREDIT POINT
                |--------------------------------------------------------------------------
                */

                /*
                |--------------------------------------------------------------------------
                | TOTAL CREDIT HOURS
                |--------------------------------------------------------------------------
                |
                | Exact Annual Based result-sheet rule:
                | every normal enrolled subject contributes subjects.credits
                | to Total Credit Hrs, regardless of PASS/FAIL.
                | Only Credit Points are restricted to PASS subjects.
                | NC subjects are excluded completely.
                |
                */

                $creditValue = (float) ($subject->credits ?? 0);

                $totalCreditHours += $creditValue;


                if ($isPassed) {

                    $gradePoint =
                        round($total / 10, 3);


                    /*
                    |--------------------------------------------------------------------------
                    | CREDIT CALCULATION
                    |--------------------------------------------------------------------------
                    |
                    | IMPORTANT:
                    | Display credit = theory_credits + practical_credits
                    | Calculation credit = subjects.credits
                    |
                    | This is exactly how Annual Based result calculation works.
                    |
                    */

                    $creditPoint =
                        round(
                            $gradePoint * $creditValue,
                            2
                        );


                    $result = 'PASS';


                    /*
                    |--------------------------------------------------------------------------
                    | GPA CALCULATION
                    |--------------------------------------------------------------------------
                    */

                    $totalCreditPoints += $creditPoint;

                } else {

                    $gradePoint = null;

                    $creditPoint = null;

                    $result = 'FAIL';


                    /*
                    |--------------------------------------------------------------------------
                    | FAILED SUBJECT
                    |--------------------------------------------------------------------------
                    */

                    $failedSubjects->push([
                        'subject_name' =>
                            $subject->name
                            ?? $subject->subject_name
                            ?? '-',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | SAVE DMC SUBJECT DATA
                |--------------------------------------------------------------------------
                */

                $dmcSubjects[] = [

                    'subject' =>
                        $subject,

                    'is_nc' =>
                        false,

                    'credit_display' =>
                        $creditDisplay,

                    'internal1' =>
                        $internal1Final,

                    'internal2' =>
                        $internal2Final,

                    'theory' =>
                        $theory1 + $theory2,

                    'practical' =>
                        $practical1 + $practical2,

                    'total' =>
                        $total,

                    'grade_point' =>
                        $gradePoint,

                    'credit_point' =>
                        $creditPoint,

                    'result' =>
                        $result,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | GPA
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | ANNUAL BASED GPA
            |--------------------------------------------------------------------------
            |
            | Same as Annual Based result blade:
            |
            | GPA = Total Credit Points / Total Credits
            |
            | Failed subjects do NOT contribute credit points or credits.
            |
            */

            $gpa = $totalCreditHours > 0
                ? round(
                    $totalCreditPoints / $totalCreditHours,
                    3
                )
                : 0;


            /*
            |--------------------------------------------------------------------------
            | RESULT
            |--------------------------------------------------------------------------
            */

            $failedSubjectCount =
                $failedSubjects->count();


            if ($failedSubjectCount === 0) {

                $resultText = 'PASS';

            } elseif ($failedSubjectCount <= 2) {

                $failedSubjectNames =
                    $failedSubjects
                        ->pluck('subject_name')
                        ->implode(', ');


                $resultText =
                    'Eligible for compartment in '
                    . $failedSubjectNames;

            } else {

                $resultText = 'FAIL';
            }

        @endphp


        {{-- ============================================================
             DMC PAGE
             ============================================================ --}}

        <div class="compartment-sheet table-responsive dmc-page">

            {{-- WATERMARK --}}

            <div class="compartment-watermark">

                <img
                    src="{{ asset('assets/images/watermark.png') }}"
                    alt="Watermark"
                >

            </div>


            {{-- DMC TABLE --}}

            <table class="table compartment-table compartment-result-table">

                <thead class="table-header">


                    {{-- =================================================
                         HEADER INFORMATION
                         ================================================= --}}

                    <tr class="no-border">

                        <td colspan="9" class="no-border">


                            {{-- SR NO / ADMISSION NO --}}

                            <div class="flex-between">

                                <p>

                                    Sr. No. {{-- $num++ --}}

                                    <span>
                                       <u>&nbsp; &nbsp; {{ $srNo }} &nbsp; &nbsp;</u>
                                    </span>

                                </p>


                                <p>

                                    Admission No.

                                    <span>
                                       <u><b>&nbsp; &nbsp; {{ $admissionNo }} &nbsp; &nbsp;</b></u>
                                    </span>

                                </p>

                            </div>


                            <center>

                                <h4 class="font-40">
                                    GURU ANGAD DEV VETERINARY AND ANIMAL
                                    SCIENCES UNIVERSITY, LUDHIANA (INDIA)
                                </h4>


                                <img
                                    src="{{ asset('assets/images/watermark.png') }}"
                                    alt="College Logo"
                                    class="clg-logo"
                                >


                                <h4 class="margin-b25 font-40">
                                    DETAILED MARKS CERTIFICATE
                                </h4>


                                <h4 class="margin-b25 font-40">
                                    FIRST PROFESSIONAL ({{ $course->name ?? '-' }}) {{ $exam->exam_year ?? '-' }}
                                </h4>


                                <h4 class=" font-40">
                                    {{ optional($institute)->name
                                        ?? 'College Name Display...........' }}
                                </h4>

                            </center>


                            {{-- PERSONAL DETAILS --}}

                            <div class="header_info">

                                <div class="personal-detail">

                                    <span>Name:</span>

                                    <b>
                                        {{ $student->student_name
                                            ?? $student->name
                                            ?? '-' }}
                                    </b>

                                </div>


                                <div class="personal-detail">

                                    <span>Batch:</span>

                                    <b>
                                        {{ $batchName }}
                                    </b>

                                </div>

                            </div>


                            <div class="header_info">

                                <div class="personal-detail">
                            
                                    <span>Father's Name:</span>
                            
                                    <b>
                                        Mr. {{ ucwords(strtolower(
                                            preg_replace(
                                                '/^Mr\.?\s*/i',
                                                '',
                                                trim($student->father_name ?? '')
                                            )
                                        )) }}
                                    </b>
                            
                                </div>
                            
                            
                                <div class="personal-detail">
                            
                                    <span>Mother's Name:</span>
                            
                                    <b>
                                        Ms. {{ ucwords(strtolower(
                                            preg_replace(
                                                '/^Ms\.?\s*/i',
                                                '',
                                                trim($student->mother_name ?? '')
                                            )
                                        )) }}
                                    </b>
                            
                                </div>
                            
                            </div>

                        </td>

                    </tr>


                    {{-- =================================================
                         TABLE HEADER - ROW 1
                         ================================================= --}}

                    <tr class="top-bottom-border">

                        <th rowspan="3">
                            Subject
                        </th>


                        <th rowspan="3">
                            Credit
                            <br>
                            Hrs.
                        </th>


                        <th colspan="4">
                            Marks Obtained
                        </th>


                        <th rowspan="3">
                            Total
                            <br>
                            ({{ number_format($bestInternalMaximum + $dmcTheoryMaximum + $dmcPracticalMaximum, 0) }})
                        </th>


                        <th rowspan="3">
                            Grade
                            <br>
                            Point
                            <br>
                            (10 Point
                            <br>
                            Basis)
                        </th>


                        <th rowspan="3">
                            Credit
                            <br>
                            Points
                        </th>

                    </tr>


                    {{-- =================================================
                         TABLE HEADER - ROW 2
                         ================================================= --}}

                    <tr class="top-bottom-border">

                        <th colspan="2">
                            Internal Assessment
                        </th>


                        <th colspan="2">
                            Annual
                        </th>

                    </tr>


                    {{-- =================================================
                         TABLE HEADER - ROW 3
                         ================================================= --}}

                    <tr class="top-bottom-border">

                        <th>
                            First
                            <br>
                            ({{ number_format($bestInternalMaximum1, 0) }})
                        </th>


                        <th>
                            Second
                            <br>
                            ({{ number_format($bestInternalMaximum2, 0) }})
                        </th>


                        <th>
                            Theory
                            <br>
                            ({{ number_format($dmcTheoryMaximum, 0) }})
                        </th>


                        <th>
                            Practical
                            <br>
                            ({{ number_format($dmcPracticalMaximum, 0) }})
                        </th>

                    </tr>

                </thead>


                {{-- ========================================================
                     TABLE BODY
                     ======================================================== --}}

                <tbody>

                    @forelse($dmcSubjects as $dmcSubject)

                        @php

                            $subject =
                                $dmcSubject['subject'];

                            $subjectName =
                                $subject->name
                                ?? $subject->subject_name
                                ?? '-';

                            $creditDisplay =
                                $dmcSubject['credit_display'];

                        @endphp


                        {{-- =================================================
                             NC SUBJECT
                             ================================================= --}}

                        @if($dmcSubject['is_nc'])

                            <tr>

                                <td class="text-left">

                                    {{ $subjectName }}

                                </td>


                                <td>

                                    {!! $creditDisplay !!}

                                    <br>

                                    (NC)

                                </td>


                                <td colspan="7">

                                    {{ $dmcSubject['nc_grade'] }}

                                </td>

                            </tr>


                        {{-- =================================================
                             NORMAL SUBJECT
                             ================================================= --}}

                        @else

                            @php

                                $result =
                                    strtoupper(
                                        trim(
                                            (string)
                                            $dmcSubject['result']
                                        )
                                    );


                                $gradePointDisplay =
                                    $result === 'FAIL'
                                        ? '-'
                                        : number_format(
                                            (float)
                                            $dmcSubject['grade_point'],
                                            3
                                        );


                                $creditPointDisplay =
                                    $result === 'FAIL'
                                        ? 'Fail'
                                        : number_format(
                                            (float)
                                            $dmcSubject['credit_point'],
                                            2
                                        );

                            @endphp


                            <tr>

                                {{-- SUBJECT NAME --}}

                                <td class="text-left">

                                    {{ $subjectName }}

                                </td>


                                {{-- CREDIT --}}

                                <td>

                                    {!! $creditDisplay !!}

                                </td>


                                {{-- INTERNAL 1 --}}

                                <td>

                                    {{ $formatMark(
                                        $dmcSubject['internal1']
                                    ) }}

                                </td>


                                {{-- INTERNAL 2 --}}

                                <td>

                                    {{ $formatMark(
                                        $dmcSubject['internal2']
                                    ) }}

                                </td>


                                {{-- THEORY --}}

                                <td>

                                    {{ $formatMark(
                                        $dmcSubject['theory']
                                    ) }}

                                </td>


                                {{-- PRACTICAL --}}

                                <td>

                                    {{ $formatMark(
                                        $dmcSubject['practical']
                                    ) }}

                                </td>


                                {{-- TOTAL --}}

                                <td>

                                    {{ $formatMark(
                                        $dmcSubject['total']
                                    ) }}

                                </td>


                                {{-- GRADE POINT --}}

                                <td>

                                    {{ $gradePointDisplay }}

                                </td>


                                {{-- CREDIT POINT --}}

                                <td>

                                    {{ $creditPointDisplay }}

                                </td>

                            </tr>

                        @endif

                    @empty

                        <tr>

                            <td colspan="9">
                                No result data found.
                            </td>

                        </tr>

                    @endforelse

                </tbody>


                {{-- ========================================================
                     FOOTER
                     ======================================================== --}}

                <tfoot>


                    <tr>

                        <td colspan="9">

                            <br>

                            <div class="flex-between">

                                <p>

                                    CURRENT :
                                    Total Credit Hrs:
                                    <b>{{ number_format(
                                        $totalCreditHours,
                                        0
                                    ) }}</b>

                                </p>


                                <p>

                                    Total Credit Points Earned:
                                    <b>{{ number_format(
                                        $totalCreditPoints,
                                        2
                                    ) }}</b>

                                </p>


                                <p>

                                    GPA:
                                    <b>{{ number_format(
                                        $gpa,
                                        3
                                    ) }}/10.000</b>

                                </p>

                            </div>


                            <br>


                            &nbsp;&nbsp;&nbsp;

                            RESULT :

                            <b>
                                {{ $resultText }}
                            </b>

                        </td>

                    </tr>


                    {{-- =================================================
                         DATE / SIGNATURE
                         ================================================= --}}

                    <tr class="bottom-signature">

                        <td colspan="3">
                            Date:
                            {{ \Carbon\Carbon::parse($printDate)->format('d-m-Y') }} {{-- now()->format('d-m-Y') --}}

                        </td>


                        <td colspan="6" class="text-right">

                            Assistant Registrar(Academics)

                            <br>

                            for Registrar

                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>

    @empty

        <div class="alert alert-warning text-center">

            No student found for DMC.

        </div>

    @endforelse

</div>
