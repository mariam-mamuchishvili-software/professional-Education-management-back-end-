<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollegeRequest;
use App\Http\Requests\UpdateCollegeRequest;
use App\Http\Resources\CollegeResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\TeacherResource;
use App\Models\College;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\Cloudinary\CloudinaryUploader;
use Illuminate\Http\Request;

class CollegeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $skip = max((int) $request->query('skip', 0), 0);
        $limit = max((int) $request->query('limit', 30), 1);
        $includes = $this->resolveIncludes($request, $this->allowedIncludes());

        $colleges = College::with([...$this->eagerLoadableIncludes($includes), 'detail.socialLinks', 'slides', 'professions'])
            ->skip($skip)->take($limit)->get();

        $colleges->each(fn (College $college) => $this->attachComputedIncludes($college, $includes));

        return CollegeResource::collection($colleges)->additional([
            'total' => College::count(),
            'skip' => $skip,
            'limit' => $limit,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCollegeRequest $request)
    {
        $data = $request->validated();

        foreach (['poster', 'logo'] as $imageField) {
            if ($request->hasFile($imageField)) {
                $data[$imageField] = app(CloudinaryUploader::class)->upload($request->file($imageField), 'eduhub/colleges');
            }
        }

        $college = College::create($data);

        return new CollegeResource($college);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id)
    {
        $includes = $this->resolveIncludes($request, $this->allowedIncludes());

        $college = College::with([...$this->eagerLoadableIncludes($includes), 'detail.socialLinks', 'slides', 'professions'])->find($id);

        if (! $college) {
            return response()->json(['message' => 'College not found'], 404);
        }

        $this->attachComputedIncludes($college, $includes);

        return new CollegeResource($college);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCollegeRequest $request, College $college)
    {
        $data = $request->validated();

        foreach (['poster', 'logo'] as $imageField) {
            if ($request->hasFile($imageField)) {
                $data[$imageField] = app(CloudinaryUploader::class)->upload($request->file($imageField), 'eduhub/colleges');
            }
        }

        $college->update($data);

        return new CollegeResource($college);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(College $college)
    {
        $college->delete();

        return response()->json([
            'message' => 'College deleted successfully',
        ]);
    }

    /**
     * Display the teachers belonging to the specified college.
     */
    public function teachers(College $college)
    {
        return TeacherResource::collection($college->teachers);
    }

    /**
     * Attach a teacher to the specified college.
     */
    public function attachTeacher(Request $request, College $college)
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
        ]);

        $college->teachers()->syncWithoutDetaching([$validated['teacher_id']]);

        return TeacherResource::collection($college->teachers);
    }

    /**
     * Detach a teacher from the specified college.
     */
    public function detachTeacher(College $college, Teacher $teacher)
    {
        $college->teachers()->detach($teacher);

        return response()->json([
            'message' => 'Teacher detached from college successfully',
        ]);
    }

    /**
     * Display the students belonging to the specified college.
     */
    public function students(College $college)
    {
        return StudentResource::collection($college->students);
    }

    /**
     * Attach a student to the specified college.
     */
    public function attachStudent(Request $request, College $college)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        $college->students()->syncWithoutDetaching([$validated['student_id']]);

        return StudentResource::collection($college->students);
    }

    /**
     * Detach a student from the specified college.
     */
    public function detachStudent(College $college, Student $student)
    {
        $college->students()->detach($student);

        return response()->json([
            'message' => 'Student detached from college successfully',
        ]);
    }

    /**
     * Allowlist tree of relation paths that may be requested via ?include=, up to 3 levels deep.
     * 'groups' isn't a native Eloquent relation on College (see College::relatedGroups()),
     * so it's excluded via self::COMPUTED_INCLUDES before being passed to with() — see
     * eagerLoadableIncludes(). 'professions' is always eager loaded, so it isn't listed here.
     *
     * @return array<string, array<mixed>>
     */
    private function allowedIncludes(): array
    {
        return [
            'teachers' => [
                'modules' => [
                    'students' => [],
                    'professions' => [],
                ],
            ],
            'groups' => [],
            'students' => [],
        ];
    }

    /**
     * Include paths that aren't real Eloquent relations and must be resolved manually
     * instead of being passed to with().
     *
     * @var array<int, string>
     */
    private const COMPUTED_INCLUDES = ['groups'];

    /**
     * @param  array<int, string>  $includes
     * @return array<int, string>
     */
    private function eagerLoadableIncludes(array $includes): array
    {
        return array_values(array_diff($includes, self::COMPUTED_INCLUDES));
    }

    /**
     * Attach the computed (non-Eloquent-relation) includes requested for this college.
     *
     * @param  array<int, string>  $includes
     */
    private function attachComputedIncludes(College $college, array $includes): void
    {
        if (in_array('groups', $includes, true)) {
            $college->setRelation('groups', $college->relatedGroups()->get());
        }
    }
}
