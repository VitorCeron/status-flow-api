<?php

namespace App\Http\Requests\Profile;

use App\Enums\TimezoneEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'string', 'min:2', 'max:255'],
            'timezone' => ['sometimes', Rule::enum(TimezoneEnum::class)],
        ];
    }

    /**
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $fields = ['name', 'timezone'];

            $hasAny = collect($fields)->some(fn ($field) => $this->has($field));

            if (! $hasAny) {
                $validator->errors()->add('general', 'At least one field (name, timezone) must be provided.');
            }
        });
    }
}
