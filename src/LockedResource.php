<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use Symfony\Component\Lock\LockInterface;

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
    protected function __construct(
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
     * @template U of mixed
     *
     * @param U $resource
     *
     * @return Locked<U>
     */
    public static function of(
        mixed $resource,
        LockInterface $lock1,
        LockInterface ...$lockN,
    ): self {
        /** @var Locked<U> */
        return new Symfony\Lock\LockedResource($resource, $lock1, ...$lockN);
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
