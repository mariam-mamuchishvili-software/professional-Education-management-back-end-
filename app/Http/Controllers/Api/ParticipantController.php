<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreParticipantRequest;
use App\Http\Requests\UpdateParticipantRequest;
use App\Http\Resources\ParticipantResource;
use App\Models\Participant;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $skip = max((int) $request->query('skip', 0), 0);
        $limit = max((int) $request->query('limit', 30), 1);

        return ParticipantResource::collection(
            Participant::skip($skip)->take($limit)->get()
        )->additional([
            'total' => Participant::count(),
            'skip' => $skip,
            'limit' => $limit,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreParticipantRequest $request)
    {
        $participant = Participant::create($request->validated());

        return new ParticipantResource($participant);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $participant = Participant::find($id);

        if (! $participant) {
            return response()->json(['message' => 'Participant not found'], 404);
        }

        return new ParticipantResource($participant);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateParticipantRequest $request, Participant $participant)
    {
        $participant->update($request->validated());

        return new ParticipantResource($participant);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Participant $participant)
    {
        $participant->delete();

        return response()->json([
            'message' => 'Participant deleted successfully',
        ]);
    }
}
