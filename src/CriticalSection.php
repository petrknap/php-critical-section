<?php declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use Symfony\Component\Lock\LockInterface;

final class CriticalSection
{
    public static function withLock(LockInterface $lock, bool $isBlocking = true): CriticalSectionInterface
    {
        return new SymfonyLockCriticalSection($lock, $isBlocking);
    }
}
