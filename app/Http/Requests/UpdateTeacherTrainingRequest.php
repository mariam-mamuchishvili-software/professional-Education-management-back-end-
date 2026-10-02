<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesDateRange;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateTeacherTrainingRequest extends FormRequest
{
    use ValidatesDateRange;

    /**
     * Determine if the user is authorized to make this request. The policy's full response is
     * returned so another teacher's record is reported as not found rather than forbidden.
     */
    public function authorize(): Response
    {
        return Gate::inspect('update', $this->route('training'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * A certificate is either uploaded as a file (stored on Cloudinary) or given as a link, not both.
     * Sending certificate_url as null removes the current certificate.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'organizer' => ['sometimes', 'string', 'max:255'],
            'certificate_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'issue_date' => ['sometimes', 'nullable', 'date'],
            'expiry_date' => array_filter(['sometimes', 'nullable', 'date', $this->onOrAfterStartDate('issue_date', $this->route('training'))]),
            'certificate' => ['sometimes', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'certificate_url' => ['sometimes', 'nullable', 'url:http,https', 'max:2048', 'prohibits:certificate'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
