<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainingRequest;
use App\Http\Requests\UpdateTrainingRequest;
use App\Http\Resources\TrainingResource;
use App\Models\Training;
use App\Services\Cloudinary\CloudinaryUploader;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $skip = max((int) $request->query('skip', 0), 0);
        $limit = max((int) $request->query('limit', 30), 1);

        return TrainingResource::collection(
            Training::skip($skip)->take($limit)->get()
        )->additional([
            'total' => Training::count(),
            'skip' => $skip,
            'limit' => $limit,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTrainingRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('poster')) {
            $data['poster'] = app(CloudinaryUploader::class)->upload($request->file('poster'), 'eduhub/trainings');
        }

        $training = Training::create($data);

        return new TrainingResource($training);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $training = Training::find($id);

        if (! $training) {
            return response()->json(['message' => 'Training not found'], 404);
        }

        return new TrainingResource($training);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTrainingRequest $request, Training $training)
    {
        $data = $request->validated();

        if ($request->hasFile('poster')) {
            $data['poster'] = app(CloudinaryUploader::class)->upload($request->file('poster'), 'eduhub/trainings');
        }

        $training->update($data);

        return new TrainingResource($training);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Training $training)
    {
        $training->delete();

        return response()->json([
            'message' => 'Training deleted successfully',
        ]);
    }
}
