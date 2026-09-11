<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'specialization' => $this->specialization,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'colleges' => CollegeResource::collection($this->whenLoaded('colleges')),
            'modules' => ModuleResource::collection($this->whenLoaded('modules')),
        ];
    }
}
