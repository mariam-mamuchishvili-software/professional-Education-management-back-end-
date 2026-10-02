<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesDateRange;
use App\Models\TeacherTraining;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherTrainingRequest extends FormRequest
{
    use ValidatesDateRange;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', TeacherTraining::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * A certificate is either uploaded as a file (stored on Cloudinary) or given as a link, not both.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'organizer' => ['required', 'string', 'max:255'],
            'certificate_number' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => array_filter(['nullable', 'date', $this->onOrAfterStartDate('issue_date')]),
            'certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'certificate_url' => ['nullable', 'url:http,https', 'max:2048', 'prohibits:certificate'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
