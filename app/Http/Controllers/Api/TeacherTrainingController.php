<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherTrainingRequest;
use App\Http\Requests\UpdateTeacherTrainingRequest;
use App\Http\Resources\TeacherTrainingResource;
use App\Models\TeacherTraining;
use App\Services\Cloudinary\CloudinaryUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeacherTrainingController extends Controller
{
    /**
     * Display the signed-in teacher's trainings and certificates.
     */
    public function index(Request $request)
    {
        return TeacherTrainingResource::collection(
            $this->authenticatedTeacher($request)->trainings()->latest('id')->get()
        );
    }

    /**
     * Store a newly created resource for the signed-in teacher.
     */
    public function store(StoreTeacherTrainingRequest $request)
    {
        $training = $this->authenticatedTeacher($request)->trainings()->create($this->attributes($request));

        return new TeacherTrainingResource($training);
    }

    /**
     * Display the specified resource.
     */
    public function show(TeacherTraining $training)
    {
        Gate::authorize('view', $training);

        return new TeacherTrainingResource($training);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherTrainingRequest $request, TeacherTraining $training)
    {
        $training->update($this->attributes($request));

        return new TeacherTrainingResource($training);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeacherTraining $training)
    {
        Gate::authorize('delete', $training);

        $training->delete();

        return response()->json([
            'message' => 'Training deleted successfully',
        ]);
    }

    /**
     * The validated attributes, with an uploaded certificate swapped for its Cloudinary URL.
     *
     * @return array<string, mixed>
     */
    private function attributes(StoreTeacherTrainingRequest|UpdateTeacherTrainingRequest $request): array
    {
        $data = $request->safe()->except('certificate');

        if ($request->hasFile('certificate')) {
            $data['certificate_url'] = app(CloudinaryUploader::class)->upload($request->file('certificate'), 'eduhub/teacher-certificates');
        }

        return $data;
    }
}
