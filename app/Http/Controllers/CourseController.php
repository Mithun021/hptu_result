<?php

namespace App\Http\Controllers;

use App\Models\AnnualSemester;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Institute;
use App\Models\Scheme;
use App\Models\Semester;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $courses = Course::with(['institutes', 'schemes', 'semesters','batches'])
        //                 ->orderBy('name', 'ASC')
        //                 ->get();

        // dd($courses);
        return view('backend.courses.index',[
            'courses' => Course::with(['institutes', 'schemes', 'semesters', 'annual_semesters','batches'])
                        ->orderBy('name', 'ASC')
                        ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.courses.create',[
            'schemes'    => Scheme::orderBy('name')->get(),
            'semesters'  => Semester::orderBy('name')->get(),
            'annual_semesters'  => AnnualSemester::orderBy('name')->get(),
            'batches'    => Batch::orderBy('name')->get(),
            'institutes' => Institute::where('account_type', 'institute')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'course' => 'required|in:UG,Diploma',
            'name' => 'required|string|max:255',
            'course_code' => 'nullable|string|max:100',
            'course_type' => 'required|string',
            'semester' => 'array|nullable',
            'annual_semester' => 'array|nullable',
            'scheme' => 'array|nullable',
            'institute' => 'array|nullable',
            'batch' => 'array|nullable',
        ]);

        $course = Course::create([
            'course' => $validated['course'],
            'name' => $validated['name'],
            'course_code' => $validated['course_code'],
            'course_type' => $validated['course_type'],
        ]);

        if (!empty($validated['semester'])) {
            $course->semesters()->sync($validated['semester']);
        }

        if (!empty($validated['annual_semester'])) {
            $course->annual_semesters()->sync($validated['annual_semester']);
        }

        if (!empty($validated['scheme'])) {
            $course->schemes()->sync($validated['scheme']);
        }

        if (!empty($validated['institute'])) {
            $course->institutes()->sync($validated['institute']);
        }

        if (!empty($validated['batch'])) {
            $course->batches()->sync($validated['batch']);
        }

        return to_route('courses.index')->with('success', 'Course created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $id)
    {
        $course = Course::with(['institutes', 'schemes', 'semesters','batches', 'annual_semesters'])->findOrFail($id);

        return view('backend.courses.edit', [
            'course' => $course,
            // 'institutes' => Institute::all(),
            'institutes' => Institute::where('account_type', 'institute')->orderBy('name')->get(),
            'semesters' => Semester::all(),
            'annual_semesters' => AnnualSemester::all(),
            'schemes' => Scheme::all(),
            'batches' => Batch::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        $request->validate([
            'course' => 'required|in:UG,Diploma',
            'name' => 'required|string|max:255',
            'course_code' => 'nullable|string|max:100',
            'course_type' => 'required|string',
            'semester' => 'nullable|array',
            'semester.*' => 'integer|exists:semesters,id',
            'annual_semester' => 'nullable|array',
            'annual_semester.*' => 'integer|exists:annual_semesters,id',
            'scheme' => 'nullable|array',
            'scheme.*' => 'integer|exists:schemes,id',
            'batch' => 'nullable|array',
            'batch.*' => 'integer|exists:batches,id',
            'institute' => 'nullable|array',
            'institute.*' => 'integer|exists:institutes,id',
        ]);

        $course = Course::findOrFail($id);
        $course->update(['course' => $request->course,'name' => $request->name,'course_code' => $request['course_code'], 'course_type' => $request['course_type'],]);

        // Sync Pivot Relations
        $course->semesters()->sync($request->semester ?? []);
        $course->annual_semesters()->sync($request->annual_semester ?? []);
        $course->schemes()->sync($request->scheme ?? []);
        $course->institutes()->sync($request->institute ?? []);
        $course->batches()->sync($request->batch ?? []);

        return to_route('courses.index')->with('success', 'Course update successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        $course = Course::findOrFail($id);

        // Detach all mapped relations
        $course->semesters()->detach();
        $course->annual_semesters()->detach();
        $course->schemes()->detach();
        $course->institutes()->detach();

        // Delete the course
        $course->delete();
        return to_route('courses.index')->with('success', 'Course update successfully!');
    }


    // Dynamic Data fetch------------------------------------------------
    public function getByCourse($id)
    {
        $course = Course::with([
            'semesters:id,name',
            'annual_semesters:id,name',
            'schemes:id,name',
            'institutes:id,name',
            'batches:id,name'
        ])->findOrFail($id);

        return response()->json([
            'semesters' => $course->semesters,
            'annual_semesters' => $course->annual_semesters,
            'schemes'   => $course->schemes,
            'institutes'   => $course->institutes,
            'batches'   => $course->batches
        ]);
    }
    
}
