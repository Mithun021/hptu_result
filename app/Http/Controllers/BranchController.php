<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Institute;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('backend.branches.index', [
            'courses' => Course::select('id', 'name')->get(),
            'branches' => Branch::with(['course:id,name', 'institutes:id,name'])->get(),
            'institutes' => Institute::select('id', 'name')->where('account_type','institute')->get(),
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
            'course_id' => ['required', 'exists:courses,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches')->where(function ($query) use ($request) {
                    return $query->where('course_id', $request->course_id);
                }),
            ],
            'branch_code' => 'nullable|string',
            'institute' => 'required|array',
            'institute.*' => 'exists:institutes,id',
        ]);

        try {
            $branch = Branch::create([
                'name'     => $request->name,
                'branch_code'     => $request->branch_code,
                'course_id' => $request->course_id
            ]);
            $branch->institutes()->sync($request->institute);

            return to_route('branches.index')->with('success', 'Branch created successfully!');
        } catch (QueryException $e) {
            return back()->withInput()->with('error', 'Database error: '.$e->getMessage());
        } catch (Exception $e) {
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
        $branch = Branch::with(['course', 'institutes'])->findOrFail($id);
        return view('backend.branches.edit',[
            'courses' => Course::select('id', 'name')->get(),
            'branches' => Branch::with('course:id,name')->get(),
            'branch' => $branch,
            'institutes' => Institute::select('id', 'name')->where('account_type','institute')->get(),
            'selectedInstitutes' => $branch->institutes->pluck('id')->toArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches')->where(function ($query) use ($request) {
                    return $query->where('course_id', $request->course_id);
                })->ignore($id),
            ],
            'branch_code' => 'nullable|string|max:50',
            'institute' => 'required|array',
            'institute.*' => 'exists:institutes,id',
        ]);

        try {
            $branch = Branch::findOrFail($id);

            $branch->update([
                'name' => $request->name,
                'branch_code' => $request->branch_code,
                'course_id' => $request->course_id
            ]);

            $branch->institutes()->sync($request->institute);

            return to_route('branches.index')->with('success', 'Branch Update successfully!');
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
            $branch = Branch::findOrFail($id);
            if ($branch) {
                $branch->institutes()->detach();
                Branch::destroy($id);
                return to_route('branches.index')->with('success', 'Branch Delete successfully!');
            }else{
                return to_route('branches.index')->with('eroor', 'Branch not found!');
            }
        } catch (QueryException $e) {
            return back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }





    // Dynamic Data Fetch------------------------------------
    public function getByCourse($course_id)
    {
        $branches = Branch::where('course_id', $course_id)->select('id', 'name')->get();
        return response()->json($branches);
    }

    public function getBranchesByCourse($course_id)
    {
        $user = auth('institute')->user();

        $institute_id = $user->account_type === 'institute'
                            ? $user->id
                            : $user->institute_id;

        $branches = Branch::where('course_id', $course_id)
            ->whereHas('institutes', function ($q) use ($institute_id) {
                $q->where('institutes.id', $institute_id);
            })
            ->select('id', 'name')
            ->get();

        return response()->json($branches);
    }

}
