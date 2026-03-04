<?php

namespace App\Exceptions\Backoffice;

use Exception;

class UserNotFoundException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'User not found.';

    /**
     * @var integer
     */
    protected $code = 404;
}
