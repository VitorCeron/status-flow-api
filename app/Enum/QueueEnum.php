<?php

namespace App\Enums;

enum QueueEnum: string
{
    case LOGS = 'logs';
    case DEFAULT = 'default';

    /**
     * Get queue name with environment prefix
     *
     * @return string
     */
    public function getQueueName(): string
    {
        $environment = config('app.env');
        $prefix = $environment . '-';

        return $prefix . $this->value;
    }

    /**
     * Get enabled queues based on environment
     *
     * @return array
     */
    public function getQueueInfo(): array
    {
        return match($this) {
            self::LOGS => [
                'name' => $this->getQueueName(),
                'description' => 'Process logs',
                'priority' => 'low',
                'max_tries' => 1,
                'timeout' => 30,
                'delay' => 0,
            ],
            self::DEFAULT => [
                'name' => $this->getQueueName(),
                'description' => 'Process default',
                'priority' => 'low',
                'max_tries' => 1,
                'timeout' => 30,
                'delay' => 0,
            ],
        };
    }
}
