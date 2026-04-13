<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
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
        $supplierId = $this->route('id');

        return [
            'name' => "sometimes|string|min:3|max:255|unique:suppliers,name,$supplierId",
            'phone' => "nullable|string|min:7|max:15|unique:suppliers,phone,$supplierId",
            'email' => "nullable|email|unique:suppliers,email,$supplierId",
            'address' => 'nullable|string',
            'balance' => 'sometimes|numeric|min:0',
        ];
    }
}
