<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
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
            Group::with(['profession', 'students'])->skip($skip)->take($limit)->get()
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
    public function show(int $id)
    {
        $group = Group::with(['profession', 'students'])->find($id);

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
}
