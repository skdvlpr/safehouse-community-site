<?php

namespace App\Http\Requests;

use App\Support\ContactDeskOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'desk' => ['required', 'string', Rule::in(ContactDeskOptions::deskKeys())],
            'gdpr_consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('site.pages.contact_name'),
            'email' => __('site.pages.contact_email'),
            'message' => __('site.pages.contact_message'),
            'desk' => __('site.pages.contact_desk'),
            'gdpr_consent' => __('site.pages.contact_gdpr_consent'),
        ];
    }
}
