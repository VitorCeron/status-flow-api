<?php

namespace App\Http\Requests\Monitor;

use App\Enums\MonitorIntervalEnum;
use App\Enums\MonitorMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMonitorRequest extends FormRequest
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
            'name'           => ['sometimes', 'string', 'max:255'],
            'url'            => ['sometimes', 'url', 'max:2048'],
            'method'         => ['sometimes', Rule::enum(MonitorMethodEnum::class)],
            'interval'       => ['sometimes', Rule::enum(MonitorIntervalEnum::class)],
            'timeout'        => ['sometimes', 'integer', 'min:1', 'max:60'],
            'fail_threshold' => ['sometimes', 'integer', 'min:1'],
            'notify_email'   => ['sometimes', 'email'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }
}
