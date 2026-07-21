<?php

namespace App\Exceptions;

use Exception;

class ExpenseAlreadyProcessedException extends Exception
{
    public function __construct()
    {
        parent::__construct('This expense has already been processed.');
    }
}
