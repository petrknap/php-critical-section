<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use PetrKnap\CriticalSection\Exception\CouldNotEnterCriticalSection;
use PetrKnap\CriticalSection\Exception\CouldNotLeaveCriticalSection;
use Symfony\Component\Lock\LockInterface;

/**
 * @template T
 *
 * @implements CriticalSectionInterface<T>
 */
abstract class WrappingCriticalSection implements CriticalSectionInterface
{
    /** @param CriticalSectionInterface<T>|null $wrappedCriticalSection */
    protected function __construct(
        private ?CriticalSectionInterface $wrappedCriticalSection,
    ) {
    }

    /** @inheritDoc */
    public function __invoke(callable $criticalSection)
    {
        if ($this->enter() === false) {
            return null;
        }
        try {
            if ($this->wrappedCriticalSection) {
                return ($this->wrappedCriticalSection)(static fn () => $criticalSection());
            }
            return $criticalSection();
        } finally {
            $this->leave();
        }
    }

    /** @return SymfonyLockCriticalSection<T> */
    public function withLock(LockInterface $lock, bool $isBlocking = true): SymfonyLockCriticalSection
    {
        return new SymfonyLockCriticalSection($this->getWrappingReferenceOrNull(), $lock, $isBlocking);
    }

    /**
     * @return bool false if it is occupied (non-blocking mode only)
     *
     * @throws CouldNotEnterCriticalSection
     */
    abstract protected function enter(): bool;

    /** @throws CouldNotLeaveCriticalSection */
    abstract protected function leave(): void;

    /** @return CriticalSectionInterface<T>|null */
    protected function getWrappingReferenceOrNull(): ?CriticalSectionInterface
    {
        return $this;
    }
}
