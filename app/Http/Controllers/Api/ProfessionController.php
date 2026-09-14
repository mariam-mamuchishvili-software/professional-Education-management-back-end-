<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfessionRequest;
use App\Http\Requests\UpdateProfessionRequest;
use App\Http\Resources\GroupResource;
use App\Http\Resources\ModuleResource;
use App\Http\Resources\ProfessionResource;
use App\Models\Module;
use App\Models\Profession;
use Illuminate\Http\Request;

class ProfessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $skip = max((int) $request->query('skip', 0), 0);
        $limit = max((int) $request->query('limit', 30), 1);

        return ProfessionResource::collection(
            Profession::with($this->resolveIncludes($request, $this->allowedIncludes()))->skip($skip)->take($limit)->get()
        )->additional([
            'total' => Profession::count(),
            'skip' => $skip,
            'limit' => $limit,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProfessionRequest $request)
    {
        $profession = Profession::create($request->validated());

        return new ProfessionResource($profession);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id)
    {
        $profession = Profession::with($this->resolveIncludes($request, $this->allowedIncludes()))->find($id);

        if (! $profession) {
            return response()->json(['message' => 'Profession not found'], 404);
        }

        return new ProfessionResource($profession);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfessionRequest $request, Profession $profession)
    {
        $profession->update($request->validated());

        return new ProfessionResource($profession);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profession $profession)
    {
        $profession->delete();

        return response()->json([
            'message' => 'Profession deleted successfully',
        ]);
    }

    /**
     * Display the groups belonging to the specified profession.
     */
    public function groups(Profession $profession)
    {
        return GroupResource::collection($profession->groups);
    }

    /**
     * Display the modules belonging to the specified profession.
     */
    public function modules(Profession $profession)
    {
        return ModuleResource::collection($profession->modules);
    }

    /**
     * Attach a module to the specified profession.
     */
    public function attachModule(Request $request, Profession $profession)
    {
        $validated = $request->validate([
            'module_id' => ['required', 'integer', 'exists:modules,id'],
        ]);

        $profession->modules()->syncWithoutDetaching([$validated['module_id']]);

        return ModuleResource::collection($profession->modules);
    }

    /**
     * Detach a module from the specified profession.
     */
    public function detachModule(Profession $profession, Module $module)
    {
        $profession->modules()->detach($module);

        return response()->json([
            'message' => 'Module detached from profession successfully',
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
            'modules' => [
                'teachers' => [
                    'colleges' => [],
                ],
            ],
            'groups' => [
                'students' => [],
            ],
        ];
    }
}
