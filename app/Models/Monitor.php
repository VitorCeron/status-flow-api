<?php

namespace App\Models;

use App\Enums\MonitorIntervalEnum;
use App\Enums\MonitorMethodEnum;
use App\Enums\MonitorStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Monitor extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'url',
        'method',
        'interval',
        'timeout',
        'fail_threshold',
        'notify_email',
        'is_active',
        'status',
        'last_checked_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'method'          => MonitorMethodEnum::class,
        'interval'        => MonitorIntervalEnum::class,
        'status'          => MonitorStatusEnum::class,
        'is_active'       => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
