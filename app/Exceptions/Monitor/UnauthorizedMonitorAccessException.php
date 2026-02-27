<?php

namespace App\Exceptions\Monitor;

use Exception;

class UnauthorizedMonitorAccessException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'You are not authorized to access this monitor.';

    /**
     * @var integer
     */
    protected $code = 403;
}
