<?php

namespace App\Http\Requests\Monitor;

use App\Enums\MonitorIntervalEnum;
use App\Enums\MonitorMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMonitorRequest extends FormRequest
{
    /**
     * @return boolean
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
            'name'           => ['required', 'string', 'max:255'],
            'url'            => ['required', 'url', 'max:2048'],
            'method'         => ['required', Rule::enum(MonitorMethodEnum::class)],
            'interval'       => ['required', Rule::enum(MonitorIntervalEnum::class)],
            'timeout'        => ['required', 'integer', 'min:1', 'max:60'],
            'fail_threshold' => ['required', 'integer', 'min:1'],
            'notify_email'   => ['required', 'email'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }
}
