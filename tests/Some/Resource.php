<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection\Some;

final class Resource
{
    public function __construct(
        public string $value = '',
    ) {
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
