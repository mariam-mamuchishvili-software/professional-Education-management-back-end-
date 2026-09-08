<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreModuleRequest;
use App\Http\Requests\UpdateModuleRequest;
use App\Http\Resources\ModuleResource;
use App\Models\Module;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ModuleResource::collection(
            Module::all()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreModuleRequest $request)
    {
        $module = Module::create($request->validated());

        return new ModuleResource($module);
    }

    /**
     * Display the specified resource.
     */
    public function show(Module $module)
    {
        return new ModuleResource($module);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateModuleRequest $request, Module $module)
    {
        $module->update($request->validated());

        return new ModuleResource($module);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Module $module)
    {
        $module->delete();

        return response()->json([
            'message' => 'Module deleted successfully',
        ]);
    }
}