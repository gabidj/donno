<?php

namespace App\Http\Requests;

use App\Enums\DonationBoxStatus;
use App\Enums\DonationBoxVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDonationBoxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('donation_box')->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'purpose' => ['sometimes', 'required', 'string', 'max:5000'],
            'target_amount' => ['nullable', 'numeric', 'min:1'],
            'visibility' => ['sometimes', 'required', Rule::enum(DonationBoxVisibility::class)],
            'status' => ['sometimes', 'required', Rule::enum(DonationBoxStatus::class)],
        ];
    }
}
