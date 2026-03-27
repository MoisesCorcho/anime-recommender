<?php

declare(strict_types=1);

namespace App\Exceptions;

final class InvalidStripeWebhookException extends \Exception
{
    public function __construct(string $message = 'Invalid Stripe webhook signature.')
    {
        parent::__construct($message);
    }
}
