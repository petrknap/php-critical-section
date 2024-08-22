<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection\Symfony\Lock;

use PetrKnap\CriticalSection\CriticalSection;
use PetrKnap\Optional\Optional;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

/**
 * @phpstan-type TLockPool = array<non-empty-string, LockInterface>
 */
final class LockPoolingHelper
{
    /**
     * @return TLockPool
     */
    public static function createLockPool(): array
    {
        return [];
    }

    /**
     * @param TLockPool $lockPool
     * @param Optional<float>|null $ttl
     * @param Optional<bool>|null $autoRelease
     */
    public static function getOrCreateLock(
        array &$lockPool,
        LockFactory $lockFactory,
        string $resource,
        Optional|null $ttl = null,
        Optional|null $autoRelease = null,
    ): LockInterface {
        if (array_key_exists($resource, $lockPool)) {
            return $lockPool[$resource];
        } elseif ($ttl === null && $autoRelease === null) {
            $lockPool[$resource] = $lockFactory->createLock($resource);
        } elseif ($ttl === null) {
            $lockPool[$resource] = $lockFactory->createLock($resource, autoRelease: $autoRelease->orElseThrow());
        } elseif ($autoRelease === null) {
            $lockPool[$resource] = $lockFactory->createLock($resource, $ttl->isPresent() ? $ttl->get() : null);
        } else {
            $lockPool[$resource] = $lockFactory->createLock($resource, $ttl->isPresent() ? $ttl->get() : null, $autoRelease->orElseThrow());
        }
        return $lockPool[$resource];
    }

    /**
     * @param TLockPool $lockPool
     */
    public static function createCriticalSection(array $lockPool): CriticalSection
    {
        ksort($lockPool);
        return CriticalSection::withLocks($lockPool);
    }
}
