<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesDateRange;
use App\Models\TeacherWorkExperience;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherWorkExperienceRequest extends FormRequest
{
    use ValidatesDateRange;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', TeacherWorkExperience::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organization' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => array_filter(['nullable', 'date', $this->onOrAfterStartDate('start_date'), 'prohibited_if_accepted:is_current']),
            'is_current' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
