<?php
use Illuminate\Support\Facades\Auth;
use App\Models\Exam;

/*
|--------------------------------------------------------------------------
| GET EXAM MONTH ORDER
|--------------------------------------------------------------------------
*/

if (!function_exists('getExamMonthOrder')) {

    function getExamMonthOrder()
    {
        return [
            'JAN' => 1,
            'FEB' => 2,
            'MAR' => 3,
            'APR' => 4,
            'MAY' => 5,
            'JUN' => 6,
            'JUL' => 7,
            'JULY' => 7,
            'AUG' => 8,
            'SEP' => 9,
            'OCT' => 10,
            'NOV' => 11,
            'DEC' => 12,
        ];
    }
}


/*
|--------------------------------------------------------------------------
| GET PREVIOUS EXAM SESSION IDS
|--------------------------------------------------------------------------
*/

if (!function_exists('getPreviousExamSessionIds')) {

    function getPreviousExamSessionIds($currentExamId)
    {
        $monthOrder = getExamMonthOrder();

        // Find current examination
        $currentExam = Exam::find($currentExamId);

        if (!$currentExam) {
            return [];
        }

        // Current examination year and month
        $currentYear = (int) $currentExam->exam_year;

        $currentMonth = $monthOrder[
            strtoupper(trim($currentExam->exam_month))
        ] ?? null;

        if (!$currentMonth) {
            return [];
        }

        // Find all previous examination sessions
        $previousExams = Exam::select(
            'id',
            'exam_month',
            'exam_year'
        )
        ->get()
        ->filter(function ($exam) use (
            $monthOrder,
            $currentYear,
            $currentMonth
        ) {

            $year = (int) $exam->exam_year;

            $month = $monthOrder[
                strtoupper(trim($exam->exam_month))
            ] ?? null;

            if (!$month) {
                return false;
            }

            return ($year * 100 + $month)
                < ($currentYear * 100 + $currentMonth);
        });

        // Chronological sorting
        $previousExams = $previousExams->sortBy(function ($exam) use ($monthOrder) {

            $year = (int) $exam->exam_year;

            $month = $monthOrder[
                strtoupper(trim($exam->exam_month))
            ] ?? 0;

            return ($year * 100) + $month;
        });

        // Return previous exam IDs only
        return $previousExams
            ->pluck('id')
            ->values()
            ->toArray();
    }
}


if (!function_exists('redirectIfLoggedIn')) {
    function redirectIfLoggedIn()
    {
        if (Auth::check()) {
            return redirect()->route('admindashboard.get');
        }

        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }

        if (Auth::guard('institute')->check()) {
            return redirect()->route('institute.dashboard');
        }

        return null;
    }
}
