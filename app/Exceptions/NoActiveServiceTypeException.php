<?php

namespace App\Exceptions;

use Exception;

class NoActiveServiceTypeException extends Exception
{
    public function __construct()
    {
        parent::__construct('No active wash service type configured for this site.');
    }
}
