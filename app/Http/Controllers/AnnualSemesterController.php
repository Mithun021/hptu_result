<?php

namespace App\Http\Controllers;

use App\Models\AnnualSemester;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnnualSemesterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('backend.annual-semester.index',[
            'semesters' => AnnualSemester::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        try {
            $permission = AnnualSemester::create([
                'name'     => $request->name
            ]);

            return to_route('annual-semesters.index')->with('success', 'Semester created successfully!');
        } catch (QueryException $e) {
            // Handle database-related errors
            return back()->withInput()->with('error', 'Database error: '.$e->getMessage());
        } catch (Exception $e) {
            // Handle general errors
            return back()->withInput()->with('error', 'Something went wrong: '.$e->getMessage());
        }
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
        return view('backend.annual-semester.edit',[
            'semesters' => AnnualSemester::all(),
            'semester' => AnnualSemester::findOrFail($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('semesters')->ignore($id),
            ],
        ]);

        try {
            $semester = AnnualSemester::findOrFail($id);

            $semester->name  = $request->name;

            $semester->save();

            return to_route('annual-semesters.index')->with('success', 'Semester updated successfully!');
        } catch (QueryException $e) {
            return back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        try {
            $semester = AnnualSemester::findOrFail($id);
            if ($semester) {
                AnnualSemester::destroy($id);
                return to_route('annual-semesters.index')->with('success', 'Semester Delete successfully!');
            }else{
                return to_route('annual-semesters.index')->with('eroor', 'Semester not found!');
            }
        } catch (QueryException $e) {
            // Handle database errors
            return back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
        } catch (Exception $e) {
            // Handle general errors
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
