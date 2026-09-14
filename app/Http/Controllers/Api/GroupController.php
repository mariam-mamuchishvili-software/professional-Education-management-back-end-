<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Http\Resources\StudentResource;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $skip = max((int) $request->query('skip', 0), 0);
        $limit = max((int) $request->query('limit', 30), 1);

        return GroupResource::collection(
            Group::with($this->resolveIncludes($request, $this->allowedIncludes()))->skip($skip)->take($limit)->get()
        )->additional([
            'total' => Group::count(),
            'skip' => $skip,
            'limit' => $limit,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request)
    {
        $group = Group::create($request->validated());

        return new GroupResource($group);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id)
    {
        $group = Group::with($this->resolveIncludes($request, $this->allowedIncludes()))->find($id);

        if (! $group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        return new GroupResource($group);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        $group->update($request->validated());

        return new GroupResource($group);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        $group->delete();

        return response()->json([
            'message' => 'Group deleted successfully',
        ]);
    }

    /**
     * Display the students enrolled in the specified group.
     */
    public function students(Group $group)
    {
        return StudentResource::collection($group->students);
    }

    /**
     * Enroll a student in the specified group.
     */
    public function enrollStudent(Request $request, Group $group)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        $group->students()->syncWithoutDetaching([$validated['student_id']]);

        return StudentResource::collection($group->students);
    }

    /**
     * Remove a student from the specified group.
     */
    public function removeStudent(Group $group, Student $student)
    {
        $group->students()->detach($student);

        return response()->json([
            'message' => 'Student removed from group successfully',
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
            'profession' => [
                'modules' => [
                    'teachers' => [],
                ],
            ],
            'students' => [
                'modules' => [],
            ],
        ];
    }
}
