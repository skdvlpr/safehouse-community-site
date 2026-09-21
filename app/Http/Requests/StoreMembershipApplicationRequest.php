<?php

namespace App\Http\Requests;

use App\Services\TurnstileVerifier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMembershipApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('tax_code'))) {
            $this->merge([
                'tax_code' => strtoupper(preg_replace('/\s+/', '', $this->input('tax_code')) ?? ''),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_province' => ['required', 'string', 'max:8'],
            'birth_date' => ['required', 'date', 'before:today'],
            'tax_code' => ['required', 'string', 'size:16', 'regex:/^[A-Z0-9]{16}$/'],
            'city' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:8'],
            'address' => ['required', 'string', 'max:255'],
            'cap' => ['required', 'string', 'max:10'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'accept_statute' => ['accepted'],
            'accept_mission' => ['accepted'],
            'accept_fee' => ['accepted'],
            'newsletter_consent' => ['required', Rule::in(['0', '1'])],
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
}
