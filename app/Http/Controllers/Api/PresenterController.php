<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePresenterRequest;
use App\Http\Requests\UpdatePresenterRequest;
use App\Http\Resources\PresenterResource;
use App\Models\Presenter;
use Illuminate\Http\Request;

class PresenterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $skip = max((int) $request->query('skip', 0), 0);
        $limit = max((int) $request->query('limit', 30), 1);

        return PresenterResource::collection(
            Presenter::skip($skip)->take($limit)->get()
        )->additional([
            'total' => Presenter::count(),
            'skip' => $skip,
            'limit' => $limit,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePresenterRequest $request)
    {
        $presenter = Presenter::create($request->validated());

        return new PresenterResource($presenter);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $presenter = Presenter::find($id);

        if (! $presenter) {
            return response()->json(['message' => 'Presenter not found'], 404);
        }

        return new PresenterResource($presenter);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePresenterRequest $request, Presenter $presenter)
    {
        $presenter->update($request->validated());

        return new PresenterResource($presenter);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Presenter $presenter)
    {
        $presenter->delete();

        return response()->json([
            'message' => 'Presenter deleted successfully',
        ]);
    }
}
