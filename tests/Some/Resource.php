<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection\Some;

final class Resource
{
    public function __construct(
        public readonly int $id,
        public int $value = 0,
    ) {
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
