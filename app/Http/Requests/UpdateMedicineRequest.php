<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicineRequest extends FormRequest
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
        $medicineId = $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'barcode' => ['sometimes', 'string', Rule::unique('medicines')->ignore($medicineId)],
            'category_name' => ['sometimes', 'string', 'exists:categories,name'],
            'manufacturer' => ['sometimes', 'string', 'max:255'],
            'active_ingredient' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'integer', 'min:0'],
            'production_date' => ['sometimes', 'date'],
            'expiry_date' => ['sometimes', 'date'],
        ];
    }
}
