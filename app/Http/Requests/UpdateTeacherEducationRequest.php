<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesDateRange;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateTeacherEducationRequest extends FormRequest
{
    use ValidatesDateRange;

    /**
     * Determine if the user is authorized to make this request. The policy's full response is
     * returned so another teacher's record is reported as not found rather than forbidden.
     */
    public function authorize(): Response
    {
        return Gate::inspect('update', $this->route('education'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'institution' => ['sometimes', 'string', 'max:255'],
            'degree' => ['sometimes', 'string', 'max:255'],
            'specialization' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => array_filter(['sometimes', 'nullable', 'date', $this->onOrAfterStartDate('start_date', $this->route('education'))]),
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
