<?php

declare(strict_types=1);

namespace App\Model;

class Breakpoint
{
    public function __construct(
        private readonly float $amount,
        private readonly float $fee,
    )
    {
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getFee(): float
    {
        return $this->fee;
    }
}