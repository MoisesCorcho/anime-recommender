<?php

declare(strict_types=1);

namespace App\Exceptions;

final class InsufficientCreditsException extends \Exception
{
    public function __construct(string $message = 'Insufficient credits.')
    {
        parent::__construct($message);
    }
}
