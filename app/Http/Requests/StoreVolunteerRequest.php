<?php

namespace App\Http\Requests;

use App\Services\TurnstileVerifier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreVolunteerRequest extends FormRequest
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
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:5000'],
            'gdpr_consent' => ['accepted'],
            'cf-turnstile-response' => [Rule::requiredIf(fn (): bool => app(TurnstileVerifier::class)->enabled()), 'nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $turnstile = app(TurnstileVerifier::class);

            if (! $turnstile->enabled()) {
                return;
            }

            if ($turnstile->verify($this->input('cf-turnstile-response'), $this->ip())) {
                return;
            }

            $validator->errors()->add('cf-turnstile-response', __('site.pages.contact_captcha_failed'));
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('site.volunteer.name'),
            'last_name' => __('site.volunteer.last_name'),
            'email' => __('site.volunteer.email'),
            'phone' => __('site.volunteer.phone'),
            'message' => __('site.volunteer.message'),
            'gdpr_consent' => __('site.volunteer.gdpr_consent'),
            'cf-turnstile-response' => __('site.pages.contact_captcha'),
        ];
    }
}
