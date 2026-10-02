<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherEducationRequest;
use App\Http\Requests\UpdateTeacherEducationRequest;
use App\Http\Resources\TeacherEducationResource;
use App\Models\TeacherEducation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeacherEducationController extends Controller
{
    /**
     * Display the signed-in teacher's education records.
     */
    public function index(Request $request)
    {
        return TeacherEducationResource::collection(
            $this->authenticatedTeacher($request)->educations()->latest('id')->get()
        );
    }

    /**
     * Store a newly created resource for the signed-in teacher.
     */
    public function store(StoreTeacherEducationRequest $request)
    {
        $education = $this->authenticatedTeacher($request)->educations()->create($request->validated());

        return new TeacherEducationResource($education);
    }

    /**
     * Display the specified resource.
     */
    public function show(TeacherEducation $education)
    {
        Gate::authorize('view', $education);

        return new TeacherEducationResource($education);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherEducationRequest $request, TeacherEducation $education)
    {
        $education->update($request->validated());

        return new TeacherEducationResource($education);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeacherEducation $education)
    {
        Gate::authorize('delete', $education);

        $education->delete();

        return response()->json([
            'message' => 'Education deleted successfully',
        ]);
    }
}
