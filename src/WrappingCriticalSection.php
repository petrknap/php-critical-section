<?php declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use PetrKnap\CriticalSection\Exception\CouldNotEnterCriticalSection;
use PetrKnap\CriticalSection\Exception\CouldNotLeaveCriticalSection;
use Symfony\Component\Lock\LockInterface;

/**
 * @template T
 * @implements CriticalSectionInterface<T>
 */
abstract class WrappingCriticalSection implements CriticalSectionInterface
{
    /** @param CriticalSectionInterface<T>|null $wrappedCriticalSection */
    protected function __construct(
        private ?CriticalSectionInterface $wrappedCriticalSection,
    ) {
    }

    /** @return SymfonyLockCriticalSection<T> */
    public function withLock(LockInterface $lock, bool $isBlocking = true): SymfonyLockCriticalSection
    {
        return new SymfonyLockCriticalSection($this->getOptimalThis(), $lock, $isBlocking);
    }

    /** @return CriticalSectionInterface<T>|null */
    private function getOptimalThis(): ?CriticalSectionInterface
    {
        if ($this instanceof NonCriticalSection) {
            return $this->wrappedCriticalSection;
        }
        return $this;
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

    /**
     * @return bool false if it is occupied (non-blocking mode only)
     *
     * @throws CouldNotEnterCriticalSection
     */
    abstract protected function enter(): bool;

    /** @throws CouldNotLeaveCriticalSection */
    abstract protected function leave(): void;
}
