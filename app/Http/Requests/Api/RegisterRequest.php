<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password; // <-- استيراد قاعدة كلمة المرور الجديدة

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            // --- هنا فين درنا التعديل ---
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8) // على الأقل 8 حروف
                    ->mixedCase() // خاص يكون حرف كبير وحرف صغير
                    ->numbers()   // خاص يكون رقم
                    ->symbols(),  // خاص يكون رمز (بحال @, #, $, ...)
            ],
        ];
    }
}
