<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection\Symfony\Lock;

use PetrKnap\CriticalSection\CriticalSection as Base;
use PetrKnap\CriticalSection\Exception;
use PetrKnap\CriticalSection\WrappingCriticalSection;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\LockReleasingException;
use Symfony\Component\Lock\LockInterface;

final class CriticalSection extends WrappingCriticalSection
{
    protected function __construct(
        private readonly LockInterface $lock,
        Base|null $wrappedCriticalSection,
        bool $isBlocking,
    ) {
        parent::__construct($wrappedCriticalSection, $isBlocking);
    }

    protected function enter(): bool
    {
        try {
            return $this->lock->acquire(blocking: $this->isBlocking);
        } catch (LockConflictedException | LockAcquiringException $reason) {
            throw new Exception\CouldNotEnterCriticalSection($reason);
        }
    }

    protected function leave(): void
    {
        try {
            $this->lock->release();
        } catch (LockReleasingException $reason) {
            throw new Exception\CouldNotLeaveCriticalSection($reason);
        }
    }
}
