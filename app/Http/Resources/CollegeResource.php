<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollegeResource extends JsonResource
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
            'name' => $this->name,
            'address' => $this->address,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'poster' => $this->poster,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'detail' => $this->whenLoaded('detail', fn () => $this->detail ? new CollegeDetailResource($this->detail) : null),
            'teachers' => TeacherResource::collection($this->whenLoaded('teachers')),
            'professions' => ProfessionResource::collection($this->whenLoaded('professions')),
            'groups' => GroupResource::collection($this->whenLoaded('groups')),
            'slides' => SlideResource::collection($this->slides),
        ];
    }
}
