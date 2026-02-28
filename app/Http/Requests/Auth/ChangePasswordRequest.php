<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     *
     * @return boolean
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'old_password' => ['required', 'string'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
