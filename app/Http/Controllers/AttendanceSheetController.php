<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExaminationForm;
use App\Models\PortalFeature;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceSheetController extends Controller
{


    public function index()
{
    $isInstitute = Auth::guard('institute')->check();

    $institute = $isInstitute
        ? Auth::guard('institute')->user()
        : null;

    /*
    |--------------------------------------------------------------------------
    | Select View According To Logged-in Guard
    |--------------------------------------------------------------------------
    */

    $viewPath = $isInstitute
        ? 'institute.attendance-sheet.index'
        : 'backend.attendance-sheet.index';

    /*
    |--------------------------------------------------------------------------
    | Institute Feature Access
    |--------------------------------------------------------------------------
    */

    if ($isInstitute) {

        $portalFeature = PortalFeature::first();

        if (!$portalFeature || !$portalFeature->attendance_sheet) {

            return view($viewPath, [
                'groups' => collect(),
                'semesters' => collect(),
                'isInstitute' => $isInstitute,
                'instituteName' => $institute->name ?? 'Institute',
                'attendanceRoute' => 'institute.attendance-sheet.index',
                'isDisabled' => true,
                'disabledMessage' => 'This facility is temporarily unavailable for your institute. For any enquiry or assistance, please contact the University Administrator.',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Select Attendance Sheet URL According To Guard
    |--------------------------------------------------------------------------
    */

    $attendanceRoute = $isInstitute
        ? 'institute.attendance-sheet.index'
        : 'backend.attendance-sheet.index';

    /*
    |--------------------------------------------------------------------------
    | Fetch Active Exam IDs
    |--------------------------------------------------------------------------
    */

    $activeExamIds = Exam::where('is_active', 1)
        ->pluck('id');

    /*
    |--------------------------------------------------------------------------
    | Fetch And Sort Semesters In Roman Order
    |--------------------------------------------------------------------------
    */

    $romanOrder = [
        'I'      => 1,
        'II'     => 2,
        'III'    => 3,
        'IV'     => 4,
        'V'      => 5,
        'VI'     => 6,
        'VII'    => 7,
        'VIII'   => 8,
        'IX'     => 9,
        'X'      => 10,
        'XI'     => 11,
        'XII'    => 12,
    ];

    $semesters = Semester::all()
        ->sortBy(function ($semester) use ($romanOrder) {
            $name = strtoupper(trim($semester->name));

            return $romanOrder[$name] ?? 999999;
        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Fetch Paid Forms Of Active Exams
    |--------------------------------------------------------------------------
    */

    $forms = ExaminationForm::with([
            'course',
            'scheme',
            'branch',
        ])
        ->whereIn('exam_id', $activeExamIds)
        ->where('payment_status', 1)
        ->whereNotNull('semester_id')
        ->when($isInstitute, function ($query) use ($institute) {
            $query->where('exam_center_id', $institute->id);
        })
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Group By Exam + Exam Type + Course + Scheme + Branch
    |--------------------------------------------------------------------------
    */

    $groupedForms = $forms->groupBy(function ($form) {
        return implode('_', [
            $form->exam_id ?? 0,
            $form->exam_type ?? '',
            $form->course_id ?? 0,
            $form->scheme_id ?? 0,
            $form->branch_id ?? 0,
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Prepare Semester-wise Attendance Summary
    |--------------------------------------------------------------------------
    */

    $groups = $groupedForms->map(function ($groupForms) use ($semesters) {

        $firstForm = $groupForms->first();

        $semesterCounts = $groupForms
            ->groupBy('semester_id')
            ->map(function ($semesterForms) {
                return $semesterForms->count();
            });

        $semesterData = [];

        foreach ($semesters as $semester) {

            $count = $semesterCounts->get($semester->id, 0);

            $semesterData[$semester->id] = [
                'semester_id' => $semester->id,
                'semester_name' => $semester->name,
                'count' => $count,
            ];
        }

        return [
            'exam_id' => $firstForm->exam_id,
            'exam_type' => $firstForm->exam_type,

            'course_id' => $firstForm->course_id,
            'scheme_id' => $firstForm->scheme_id,
            'branch_id' => $firstForm->branch_id,

            'course_name' => $firstForm->course->name ?? 'N/A',
            'scheme_name' => $firstForm->scheme->name ?? 'NA',
            'branch_name' => $firstForm->branch->name ?? 'NA',

            'semesters' => $semesterData,

            'total_forms' => $groupForms->count(),
        ];

    })->values();

    /*
    |--------------------------------------------------------------------------
    | Return Selected Portal View
    |--------------------------------------------------------------------------
    */

    return view($viewPath, [
        'groups' => $groups,
        'semesters' => $semesters,
        'isInstitute' => $isInstitute,
        'instituteName' => $institute->name ?? 'All Institutes',
        'attendanceRoute' => $attendanceRoute,
        'isDisabled' => false,
    ]);
}

    public function show(Request $request)
    {
        $isInstitute = Auth::guard('institute')->check();

        $institute = $isInstitute
            ? Auth::guard('institute')->user()
            : null;

        // Select view according to guard
        $viewPath = $isInstitute
            ? 'institute.attendance-sheet.detail'
            : 'backend.attendance-sheet.detail';

        // Validate URL parameters
        $validated = $request->validate([
            'exam_id'     => 'required|integer',
            'exam_type'   => 'required|string',
            'course_id'   => 'required|integer',
            'scheme_id'   => 'nullable|integer',
            'branch_id'   => 'nullable|integer',
            'semester_id' => 'required|integer',
        ]);

        // Fetch active exams
        $activeExamIds = Exam::where('is_active', 1)->pluck('id');

        // Fetch examination forms with Theory subjects only
        $forms = ExaminationForm::with([
                'exam',
                'course',
                'scheme',
                'branch',
                'semester',
                'student',
                'institute',

                // Load only Theory subjects
                'choosedSubjects' => function ($query) {
                    $query->whereHas('subject', function ($subjectQuery) {
                        $subjectQuery->where('type', 'Theory');
                    })->with('subject');
                },
            ])
            ->whereIn('exam_id', $activeExamIds)
            ->where('exam_id', $validated['exam_id'])
            ->where('exam_type', $validated['exam_type'])
            ->where('course_id', $validated['course_id'])
            ->where('scheme_id', $validated['scheme_id'])
            ->where('branch_id', $validated['branch_id'])
            ->where('semester_id', $validated['semester_id'])

            // Form must have at least one Theory subject
            ->whereHas('choosedSubjects.subject', function ($query) {
                $query->where('type', 'Theory');
            })

            ->where('payment_status', 1)
            ->when($isInstitute, function ($query) use ($institute) {
                $query->where('exam_center_id', $institute->id);
            })
            ->orderBy('id')
            ->get();

        // Return 404 if no records found
        abort_if($forms->isEmpty(), 404, 'Attendance records not found.');

        // Attendance report heading details
        $firstForm = $forms->first();

        $reportDetails = [
            'exam_name'     => $firstForm->exam->name ?? 'N/A',
            'exam_type'     => $firstForm->exam_type,
            'course_name'   => $firstForm->course->name ?? 'N/A',
            'scheme_name'   => $firstForm->scheme->name ?? 'NA',
            'branch_name'   => $firstForm->branch->name ?? 'NA',
            'semester_name' => $firstForm->semester->name ?? 'N/A',
        ];

        // Return attendance report
        return view($viewPath, [
            'forms'         => $forms,
            'reportDetails' => $reportDetails,
            'isInstitute'   => $isInstitute,
            'instituteName' => $institute->name ?? 'All Institutes',
        ]);
    }


}
