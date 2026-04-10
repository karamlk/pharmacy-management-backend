<?php

namespace App\Http\Requests\Medicine;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicineRequest extends FormRequest
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
       $isBulk = is_array($this->input()) && isset($this->input()[0]);

    if ($isBulk) {
        return [
            '*.name' => ['required', 'string', 'max:255'],
            '*.barcode' => ['required', 'string', 'unique:medicines,barcode'],
            '*.category_name' => ['required', 'string', 'exists:categories,name'],
            '*.manufacturer' => ['required', 'string', 'max:255'],
            '*.active_ingredient' => ['required', 'string', 'max:255'],
            '*.price' => ['required', 'numeric', 'min:0'],
            '*.quantity' => ['required', 'integer', 'min:0'],
            '*.production_date' => ['required', 'date', 'before:*.expiry_date'],
            '*.expiry_date' => ['required', 'date', 'after:*.production_date'],
        ];
    }

    // Single object validation
    return [
        'name' => ['required', 'string', 'max:255'],
        'barcode' => ['required', 'string', 'unique:medicines,barcode'],
        'category_name' => ['required', 'string', 'exists:categories,name'],
        'manufacturer' => ['required', 'string', 'max:255'],
        'active_ingredient' => ['required', 'string', 'max:255'],
        'price' => ['required', 'numeric', 'min:0'],
        'quantity' => ['required', 'integer', 'min:0'],
        'production_date' => ['required', 'date', 'before:expiry_date'],
        'expiry_date' => ['required', 'date', 'after:production_date'],
    ];
    }
}
