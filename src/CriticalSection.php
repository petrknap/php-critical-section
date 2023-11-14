<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use Symfony\Component\Lock\LockInterface;

/** @template T */
final class CriticalSection
{
    /** @return NonCriticalSection<T> */
    public static function create(): NonCriticalSection
    {
        return new NonCriticalSection();
    }

    /** @return WrappingCriticalSection<T> */
    public static function withLock(LockInterface $lock, bool $isBlocking = true): WrappingCriticalSection
    {
        return self::create()->withLock($lock, $isBlocking);
    }

    /**
     * @param array<LockInterface> $locks
     *
     * @return WrappingCriticalSection<T>
     */
    public static function withLocks(array $locks, bool $isBlocking = true): WrappingCriticalSection
    {
        return self::create()->withLocks($locks, $isBlocking);
    }
}
