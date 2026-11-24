<?php

namespace App\Services;

use RuntimeException;

class ShippingException extends RuntimeException
{
    public const OUT_OF_RANGE = 'out_of_range';
    public const NOT_FOUND = 'not_found';
    public const UNAVAILABLE = 'unavailable';

    public function __construct(string $message, public readonly string $reason, public readonly int $status = 422)
    {
        parent::__construct($message);
    }
}