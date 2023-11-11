<?php declare(strict_types=1);

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

    /** @return SymfonyLockCriticalSection<T> */
    public static function withLock(LockInterface $lock, bool $isBlocking = true): SymfonyLockCriticalSection
    {
        return self::create()->withLock($lock, $isBlocking);
    }
}
