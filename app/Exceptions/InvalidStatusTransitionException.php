<?php

namespace App\Exceptions;

use Exception;

class InvalidStatusTransitionException extends Exception
{
    public function __construct(string $currentStatus = '', string $targetStatus = '')
    {
        if (!empty($targetStatus)) {
            $message = "Workflow Error: Cannot transition laundry job from [{$currentStatus}] to [{$targetStatus}].";
        } else {
            $message = $currentStatus ?: "Invalid workflow state transition attempted.";
        }

        parent::__construct($message);
    }
}
