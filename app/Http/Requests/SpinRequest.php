<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpinRequest extends FormRequest
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
        $allowedBetSize = [
            0.2,
            0.4,
            0.6,
            0.8,
            1,
            1.2,
            1.6,
            2,
            2.4,
            2.8,
            3.2,
            3.6,
            4,
            5,
            6,
            8,
            10,
            14,
            18,
            24,
            32,
            40,
            60,
            80,
            100,
            110,
            120,
            130,
            140,
            150
        ];

        return [
            'bet' => 'required|in:' . implode(',', $allowedBetSize)
        ];
    }
}
