<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherWorkExperienceRequest;
use App\Http\Requests\UpdateTeacherWorkExperienceRequest;
use App\Http\Resources\TeacherWorkExperienceResource;
use App\Models\TeacherWorkExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeacherWorkExperienceController extends Controller
{
    /**
     * Display the signed-in teacher's work experience.
     */
    public function index(Request $request)
    {
        return TeacherWorkExperienceResource::collection(
            $this->authenticatedTeacher($request)->workExperiences()->latest('id')->get()
        );
    }

    /**
     * Store a newly created resource for the signed-in teacher.
     */
    public function store(StoreTeacherWorkExperienceRequest $request)
    {
        $workExperience = $this->authenticatedTeacher($request)->workExperiences()->create($request->validated());

        return new TeacherWorkExperienceResource($workExperience);
    }

    /**
     * Display the specified resource.
     */
    public function show(TeacherWorkExperience $workExperience)
    {
        Gate::authorize('view', $workExperience);

        return new TeacherWorkExperienceResource($workExperience);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherWorkExperienceRequest $request, TeacherWorkExperience $workExperience)
    {
        $workExperience->update($request->validated());

        return new TeacherWorkExperienceResource($workExperience);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeacherWorkExperience $workExperience)
    {
        Gate::authorize('delete', $workExperience);

        $workExperience->delete();

        return response()->json([
            'message' => 'Work experience deleted successfully',
        ]);
    }
}
