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

    /** @return WrappingCriticalSection<T> */
    public function withLock(LockInterface $lock, bool $isBlocking = true): WrappingCriticalSection
    {
        return new SymfonyLockCriticalSection($this->getWrappingReferenceOrNull(), $lock, $isBlocking);
    }

    /**
     * @param array<LockInterface> $locks
     *
     * @return WrappingCriticalSection<T>
     */
    public function withLocks(array $locks, bool $isBlocking = true): WrappingCriticalSection
    {
        $locks = array_reverse($locks); // reverse array to keep order of locks during wrapping
        $instance = $this;
        foreach ($locks as $lock) {
            $instance = $instance->withLock($lock, $isBlocking);
        }
        return $instance;
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
