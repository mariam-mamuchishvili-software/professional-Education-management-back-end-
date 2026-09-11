<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'profession_id' => $this->profession_id,
            'name' => $this->name,
            'code' => $this->code,
            'capacity' => $this->capacity,
            'study_shift' => $this->study_shift,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'profession' => new ProfessionResource($this->whenLoaded('profession')),
            'students' => StudentResource::collection($this->whenLoaded('students')),
        ];
    }
}
