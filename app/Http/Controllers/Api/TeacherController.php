<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Http\Resources\CollegeResource;
use App\Http\Resources\ModuleResource;
use App\Http\Resources\TeacherResource;
use App\Models\Module;
use App\Models\Teacher;
use App\Services\Cloudinary\CloudinaryUploader;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $skip = max((int) $request->query('skip', 0), 0);
        $limit = max((int) $request->query('limit', 30), 1);

        return TeacherResource::collection(
            Teacher::with($this->resolveIncludes($request, $this->allowedIncludes()))->skip($skip)->take($limit)->get()
        )->additional([
            'total' => Teacher::count(),
            'skip' => $skip,
            'limit' => $limit,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = app(CloudinaryUploader::class)->upload($request->file('image'), 'eduhub/teachers');
        }

        $teacher = Teacher::create($data);

        return new TeacherResource($teacher);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id)
    {
        $teacher = Teacher::with($this->resolveIncludes($request, $this->allowedIncludes()))->find($id);

        if (! $teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        return new TeacherResource($teacher);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = app(CloudinaryUploader::class)->upload($request->file('image'), 'eduhub/teachers');
        }

        $teacher->update($data);

        return new TeacherResource($teacher);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        return response()->json([
            'message' => 'Teacher deleted successfully',
        ]);
    }

    /**
     * Display the colleges the specified teacher belongs to.
     */
    public function colleges(Teacher $teacher)
    {
        return CollegeResource::collection($teacher->colleges);
    }

    /**
     * Display the modules taught by the specified teacher.
     */
    public function modules(Teacher $teacher)
    {
        return ModuleResource::collection($teacher->modules);
    }

    /**
     * Attach a module to the specified teacher.
     */
    public function attachModule(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'module_id' => ['required', 'integer', 'exists:modules,id'],
        ]);

        $teacher->modules()->syncWithoutDetaching([$validated['module_id']]);

        return ModuleResource::collection($teacher->modules);
    }

    /**
     * Detach a module from the specified teacher.
     */
    public function detachModule(Teacher $teacher, Module $module)
    {
        $teacher->modules()->detach($module);

        return response()->json([
            'message' => 'Module detached from teacher successfully',
        ]);
    }

    /**
     * Allowlist tree of relation paths that may be eager loaded via ?include=, up to 3 levels deep.
     *
     * @return array<string, array<mixed>>
     */
    private function allowedIncludes(): array
    {
        return [
            'colleges' => [
                'teachers' => [],
            ],
            'modules' => [
                'professions' => [
                    'groups' => [],
                ],
                'students' => [],
            ],
        ];
    }
}
