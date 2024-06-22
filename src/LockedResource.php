<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

/**
 * @template T of mixed
 *
 * @mixin T
 */
abstract class LockedResource
{
    /**
     * @param T $resource
     */
    public function __construct(
        private readonly mixed $resource,
    ) {
    }

    /**
     * @param array<mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->get()->$name(...$arguments);
    }

    public function __get(string $name): mixed
    {
        return $this->get()->$name;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->get()->$name = $value;
    }

    /**
     * @return T
     *
     * @throws Exception\CouldNotGetUnlockedResource<T>
     */
    public function get(): mixed
    {
        if (!$this->isLocked()) {
            throw new Exception\CouldNotGetUnlockedResource(
                __METHOD__,
                $this->resource,
            );
        }
        return $this->resource;
    }

    abstract protected function isLocked(): bool;
}
