<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or add your auth logic
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;
        // or $this->product if route model binding

        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:1',
            'images' => 'nullable|array', // images should be an array
            'images.*' => 'nullable|image|mimes:jpg,png,jpeg|max:2048', // each item in images array
            'slug' => [
                'nullable',
                'string',
                Rule::unique('products', 'slug')->ignore($productId),
            ],
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id', // every category id must exist
        ];
    }
}

