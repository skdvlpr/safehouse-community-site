<?php

namespace App\Http\Requests;

use App\Models\DonationCampaign;
use App\Support\DonorContact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateDonationIntentRequest extends FormRequest
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
        /** @var DonationCampaign|null $donationCampaign */
        $donationCampaign = $this->route('donationCampaign');

        return [
            'amount_cents' => [
                'required',
                'integer',
                'min:'.($donationCampaign?->min_amount_cents ?? 50),
            ],
            'donor_name' => ['required', 'string', 'max:255'],
            'donor_type' => ['required', Rule::in(['individual', 'organization'])],
            'donor_email' => ['nullable', 'string', 'email', 'max:255'],
            'donor_phone' => ['nullable', 'string', 'max:50'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $contact = DonorContact::fromInput(
                $this->input('donor_email'),
                $this->input('donor_phone'),
            );

            if (! $contact->hasChannel()) {
                $validator->errors()->add(
                    'donor_email',
                    __('Inserisci un\'email o un numero di telefono per identificare il donatore.'),
                );
            }
        });
    }

    public function donorContact(): DonorContact
    {
        return DonorContact::fromInput(
            $this->validated('donor_email'),
            $this->validated('donor_phone'),
        );
    }
}
