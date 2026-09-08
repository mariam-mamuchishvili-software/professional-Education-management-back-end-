<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfessionRequest;
use App\Http\Requests\UpdateProfessionRequest;
use App\Http\Resources\ProfessionResource;
use App\Models\Profession;

class ProfessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProfessionResource::collection(
            Profession::all()
        );
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
    public function show(Profession $profession)
    {
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