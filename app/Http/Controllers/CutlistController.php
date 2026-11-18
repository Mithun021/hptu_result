<?php

namespace App\Http\Controllers;

use App\Models\ExaminationForm;
use App\Models\PortalFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CutlistController extends Controller
{
    public function index()
    {
        $isInstitute = Auth::guard('institute')->check();

        $institute = $isInstitute
            ? Auth::guard('institute')->user()
            : null;

        // Select view according to logged-in guard
        $viewPath = $isInstitute
            ? 'institute.cutlist.index'
            : 'backend.cutlist.index';

        /*
        |--------------------------------------------------------------------------
        | Institute Feature Access
        |--------------------------------------------------------------------------
        */

        $isDisabled = false;

        if ($isInstitute) {
            $portalFeature = PortalFeature::first();

            if ($portalFeature && !$portalFeature->cutlist) {
                $isDisabled = true;
            }
        }

        // Feature disabled: show message without fetching report data.
        if ($isDisabled) {
            return view($viewPath, [
                'groups' => collect(),
                'isDisabled' => true,
                'isInstitute' => $isInstitute,
                'instituteName' => $institute->name ?? 'Institute',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Fetch Paid Examination Forms
        |--------------------------------------------------------------------------
        */

        $forms = ExaminationForm::with([
                'course',
                'scheme',
                'branch',
                'choosedSubjects.subject',
            ])
            ->where('payment_status', 1)
            ->when($isInstitute, function ($query) use ($institute) {
                $query->where('exam_center_id', $institute->id);
            })
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Group By Course + Scheme + Branch
        |--------------------------------------------------------------------------
        */

        $groupedForms = $forms->groupBy(function ($form) {
            return implode('_', [
                $form->course_id ?? 0,
                $form->scheme_id ?? 0,
                $form->branch_id ?? 0,
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Prepare Subject-wise Count
        |--------------------------------------------------------------------------
        */

        $groups = $groupedForms->map(function ($groupForms) {

            $firstForm = $groupForms->first();

            $subjectBuckets = [];

            foreach ($groupForms as $form) {

                // Every paid examination form is counted separately.
                $formId = $form->id;

                foreach ($form->choosedSubjects as $chosenSubject) {

                    $subject = $chosenSubject->subject;

                    if (!$subject) {
                        continue;
                    }

                    $subjectId = $subject->id;

                    if (!isset($subjectBuckets[$subjectId])) {
                        $subjectBuckets[$subjectId] = [
                            'subject' => $subject,
                            'form_ids' => [],
                        ];
                    }

                    // Count each examination form only once per subject.
                    $subjectBuckets[$subjectId]['form_ids'][$formId] = true;
                }
            }

            $subjects = collect($subjectBuckets)
                ->map(function ($item) {
                    return [
                        'subject' => $item['subject'],
                        'student_count' => count($item['form_ids']),
                    ];
                })
                ->sortBy(function ($item) {
                    return $item['subject']->orders ?? 999999;
                })
                ->values();

            return [
                'course_name' => $firstForm->course->name ?? 'N/A',
                'scheme_name' => $firstForm->scheme->name ?? 'NA',
                'branch_name' => $firstForm->branch->name ?? 'NA',
                'subjects' => $subjects,

                // Total paid examination forms, not unique students.
                'total_students' => $groupForms->pluck('id')->unique()->count(),
            ];

        })->values();

        return view($viewPath, [
            'groups' => $groups,
            'isDisabled' => false,
            'isInstitute' => $isInstitute,
            'instituteName' => $institute->name ?? 'All Institutes',
        ]);
    }


}
