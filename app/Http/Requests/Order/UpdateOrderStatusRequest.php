<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Order;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules()
    {
        return [
            'status' => ['required', 'string', 'in:' . implode(',', array_keys(Order::$statuses))],
            'admin_notes' => 'nullable|string|max:500',
        ];
    }
}
