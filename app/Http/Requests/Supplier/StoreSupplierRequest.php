<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
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
            'name' => 'required|string|min:3|max:255|unique:suppliers,name',
            'phone' => 'nullable|string|min:7|max:15|unique:suppliers,phone',
            'email' => 'nullable|email|unique:suppliers,email',
            'address' => 'nullable|string',
            'balance' => 'required|numeric|min:0',
        ];
    }
}
