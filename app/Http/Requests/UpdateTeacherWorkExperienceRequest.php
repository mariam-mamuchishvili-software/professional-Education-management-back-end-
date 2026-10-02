<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesDateRange;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateTeacherWorkExperienceRequest extends FormRequest
{
    use ValidatesDateRange;

    /**
     * Determine if the user is authorized to make this request. The policy's full response is
     * returned so another teacher's record is reported as not found rather than forbidden.
     */
    public function authorize(): Response
    {
        return Gate::inspect('update', $this->route('work_experience'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organization' => ['sometimes', 'string', 'max:255'],
            'position' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => array_filter(['sometimes', 'nullable', 'date', $this->onOrAfterStartDate('start_date', $this->route('work_experience')), 'prohibited_if_accepted:is_current']),
            'is_current' => ['sometimes', 'boolean'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
