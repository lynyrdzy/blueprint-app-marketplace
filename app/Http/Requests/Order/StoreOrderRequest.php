<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->isCustomer();
    }

    public function rules()
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:100',
            'customer_name' => 'required|string|min:3|max:255',
            'customer_phone' => 'required|string|regex:/^\+?[0-9]{9,15}$/',
            'customer_address' => 'required|string|min:10|max:500',
        ];
    }

    public function messages()
    {
        return [
            'items.required' => 'Cart must contain at least one item',
            'customer_phone.regex' => 'Phone number must be valid',
        ];
    }
}
