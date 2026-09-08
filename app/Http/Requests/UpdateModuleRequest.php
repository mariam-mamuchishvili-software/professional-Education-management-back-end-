<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModuleRequest extends FormRequest
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
                Rule::unique('modules', 'code')->ignore($this->module),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'duration' => ['sometimes', 'integer', 'min:1'],
            'credits' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}