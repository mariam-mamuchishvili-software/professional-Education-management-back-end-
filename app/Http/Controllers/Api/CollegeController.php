<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollegeRequest;
use App\Http\Requests\UpdateCollegeRequest;
use App\Http\Resources\CollegeResource;
use App\Models\College;

class CollegeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CollegeResource::collection(
            College::all()
        );
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
    public function show(College $college)
    {
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
}