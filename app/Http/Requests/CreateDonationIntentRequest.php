<?php

namespace App\Http\Requests;

use App\Models\DonationCampaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'comment' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
