<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dui' => ['required', 'string', 'regex:/^[0-9]{8}-[0-9]$/'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'dui.required' => 'El DUI es requerido.',
            'dui.regex' => 'El formato del DUI debe ser 00000000-0.',
            'password.required' => 'La contraseña es requerida.',
        ];
    }

    public function authenticate(): void
    {
        if (!Auth::attempt(
            $this->only('dui', 'password'),
            $this->boolean('remember')
        )) {
            throw ValidationException::withMessages([
                'dui' => trans('auth.failed'),
            ]);
        }
    }
}
