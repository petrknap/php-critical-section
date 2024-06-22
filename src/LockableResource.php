<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use Symfony\Component\Lock\LockInterface;

final class LockableResource
{
    /**
     * @template T of mixed
     *
     * @param T $resource
     *
     * @return LockedResource<T>
     */
    public static function create(
        mixed $resource,
        LockInterface $lock1,
        LockInterface ...$lockN,
    ): LockedResource {
        return new Symfony\Lock\LockedResource($resource, $lock1, ...$lockN);
    }
}
