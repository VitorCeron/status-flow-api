<?php

namespace App\Http\Requests\Monitor;

use App\Enums\MonitorMethodEnum;
use App\Enums\MonitorStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListMonitorFilterRequest extends FormRequest
{
    /**
     * @return boolean
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:100'],
            'status'    => ['nullable', Rule::enum(MonitorStatusEnum::class)],
            'is_active' => ['nullable', 'boolean'],
            'method'    => ['nullable', Rule::enum(MonitorMethodEnum::class)],
        ];
    }

    /**
     * Convert query-string boolean strings to PHP booleans before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active') && is_string($this->is_active)) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }
}
