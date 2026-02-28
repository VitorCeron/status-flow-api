<?php

namespace App\Models;

use App\Enums\MonitorStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitorLog extends Model
{
    use HasFactory, HasUuids;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'monitor_id',
        'status',
        'response_code',
        'response_time_ms',
        'checked_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status'     => MonitorStatusEnum::class,
        'checked_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }
}
