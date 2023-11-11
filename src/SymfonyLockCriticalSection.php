<?php declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use PetrKnap\CriticalSection\Exception\CouldNotEnterCriticalSection;
use PetrKnap\CriticalSection\Exception\CouldNotLeaveCriticalSection;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\LockReleasingException;
use Symfony\Component\Lock\LockInterface;

/**
 * @template T
 *
 * @implements CriticalSectionInterface<T>
 */
final class SymfonyLockCriticalSection implements CriticalSectionInterface
{
    public function __construct(
        private LockInterface $lock,
        private bool $isBlocking,
    ) {
    }

    /** @inheritDoc */
    public function __invoke(callable $criticalSection)
    {
        try {
            if ($this->lock->acquire(blocking: $this->isBlocking) === false) {
                return null;
            }
        } catch (LockConflictedException | LockAcquiringException $reason) {
            throw new CouldNotEnterCriticalSection(previous: $reason);
        }
        try {
            return $criticalSection();
        } finally {
            try {
                $this->lock->release();
            } catch (LockReleasingException $reason) {
                throw new CouldNotLeaveCriticalSection(previous: $reason);
            }
        }
    }
}
