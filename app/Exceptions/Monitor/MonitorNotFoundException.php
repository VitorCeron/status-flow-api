<?php

namespace App\Exceptions\Monitor;

use Exception;

class MonitorNotFoundException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'Monitor not found.';

    /**
     * @var integer
     */
    protected $code = 404;
}
