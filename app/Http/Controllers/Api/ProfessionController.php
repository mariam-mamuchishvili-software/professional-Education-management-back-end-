<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfessionRequest;
use App\Http\Requests\UpdateProfessionRequest;
use App\Http\Resources\ProfessionResource;
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
            Profession::with(['modules', 'groups'])->skip($skip)->take($limit)->get()
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
    public function show(int $id)
    {
        $profession = Profession::with(['modules', 'groups'])->find($id);

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
}
