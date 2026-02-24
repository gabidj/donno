<?php

namespace App\Http\Requests;

use App\Enums\DonationBoxStatus;
use App\Enums\DonationBoxVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDonationBoxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:5000'],
            'target_amount' => ['nullable', 'numeric', 'min:1'],
            'visibility' => ['required', Rule::enum(DonationBoxVisibility::class)],
            'status' => ['required', Rule::enum(DonationBoxStatus::class)],
        ];
    }
}
