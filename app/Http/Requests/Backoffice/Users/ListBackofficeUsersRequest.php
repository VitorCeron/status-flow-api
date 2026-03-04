<?php

namespace App\Http\Requests\Backoffice\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListBackofficeUsersRequest extends FormRequest
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
            'per_page'   => ['nullable', 'integer', Rule::in([10, 15, 50])],
            'search'     => ['nullable', 'string', 'max:255'],
            'is_deleted' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Convert query-string boolean strings to PHP booleans before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_deleted') && is_string($this->is_deleted)) {
            $this->merge([
                'is_deleted' => filter_var($this->is_deleted, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }

    /**
     * Return only the filter-relevant fields with non-null values.
     *
     * @return array
     */
    public function filters(): array
    {
        return array_filter(
            $this->only(['search', 'is_deleted']),
            fn ($v) => $v !== null,
        );
    }

    /**
     * Return the requested per_page value, defaulting to 10.
     *
     * @return integer
     */
    public function perPage(): int
    {
        return (int) ($this->validated('per_page') ?? 10);
    }
}
