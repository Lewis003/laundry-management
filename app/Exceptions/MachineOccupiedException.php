<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown error when attempting to double-book an occupied or offline washing machine.
 */
class MachineOccupiedException extends Exception
{
    public function __construct(string $machineName)
    {
        parent::__construct("Operational Conflict: Machine [{$machineName}] is currently occupied or under maintenance.");
    }
}



