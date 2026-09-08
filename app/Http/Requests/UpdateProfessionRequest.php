<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('professions', 'code')->ignore($this->profession),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'duration' => ['sometimes', 'integer', 'min:1'],
            'qualification' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
