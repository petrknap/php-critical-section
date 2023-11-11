<?php

declare(strict_types=1);

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
 * @extends WrappingCriticalSection<T>
 */
final class SymfonyLockCriticalSection extends WrappingCriticalSection
{
    /** @param CriticalSectionInterface<T>|null $wrappedCriticalSection */
    protected function __construct(
        ?CriticalSectionInterface $wrappedCriticalSection,
        private LockInterface $lock,
        private bool $isBlocking,
    ) {
        parent::__construct($wrappedCriticalSection);
    }

    protected function enter(): bool
    {
        try {
            return $this->lock->acquire(blocking: $this->isBlocking);
        } catch (LockConflictedException | LockAcquiringException $reason) {
            throw new CouldNotEnterCriticalSection(previous: $reason);
        }
    }

    protected function leave(): void
    {
        try {
            $this->lock->release();
        } catch (LockReleasingException $reason) {
            throw new CouldNotLeaveCriticalSection(previous: $reason);
        }
    }
}
