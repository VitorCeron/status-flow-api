<?php

namespace App\Http\Requests\Backoffice\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class BackofficeDashboardRequest extends FormRequest
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
        return [];
    }
}
