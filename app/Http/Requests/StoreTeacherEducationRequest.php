<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesDateRange;
use App\Models\TeacherEducation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherEducationRequest extends FormRequest
{
    use ValidatesDateRange;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', TeacherEducation::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'institution' => ['required', 'string', 'max:255'],
            'degree' => ['required', 'string', 'max:255'],
            'specialization' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => array_filter(['nullable', 'date', $this->onOrAfterStartDate('start_date')]),
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
