<?php

namespace App\Exceptions\Auth;

use Exception;

class InvalidCredentialsException extends Exception
{
    /**
     *
     * @var string
     */
    protected $message = 'Invalid credentials.';

    /**
     *
     * @var integer
     */
    protected $code = 401;
}
