<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollegeRequest;
use App\Http\Requests\UpdateCollegeRequest;
use App\Http\Resources\CollegeResource;
use App\Http\Resources\TeacherResource;
use App\Models\College;
use App\Models\Teacher;
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

        $colleges = College::with($this->eagerLoadableIncludes($includes))
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
        $college = College::create($request->validated());

        return new CollegeResource($college);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id)
    {
        $includes = $this->resolveIncludes($request, $this->allowedIncludes());

        $college = College::with($this->eagerLoadableIncludes($includes))->find($id);

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
        $college->update($request->validated());

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
     * Allowlist tree of relation paths that may be requested via ?include=, up to 3 levels deep.
     * 'professions' and 'groups' aren't native Eloquent relations on College (see
     * College::relatedProfessions()/relatedGroups()), so they're excluded from
     * self::COMPUTED_INCLUDES before being passed to with() — see eagerLoadableIncludes().
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
            'professions' => [],
            'groups' => [],
        ];
    }

    /**
     * Include paths that aren't real Eloquent relations and must be resolved manually
     * instead of being passed to with().
     *
     * @var array<int, string>
     */
    private const COMPUTED_INCLUDES = ['professions', 'groups'];

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
        if (in_array('professions', $includes, true)) {
            $college->setRelation('professions', $college->relatedProfessions()->get());
        }

        if (in_array('groups', $includes, true)) {
            $college->setRelation('groups', $college->relatedGroups()->get());
        }
    }
}
