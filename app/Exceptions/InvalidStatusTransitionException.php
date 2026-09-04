<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when an illegal workflow state transition is attempted.
 */
class InvalidStatusTransitionException extends Exception
{
    public function __construct(string $currentStatus, string $targetStatus)
    {
        parent::__construct("Workflow Error: Cannot transition laundry job from [{$currentStatus}] to [{$targetStatus}].");
    }
}
