<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use Symfony\Component\Lock\LockInterface;

abstract class WrappingCriticalSection extends CriticalSection
{
    protected function __construct(
        private readonly CriticalSection|null $wrappedCriticalSection,
        bool $isBlocking,
    ) {
        parent::__construct($isBlocking);
    }

    public function __invoke(callable $criticalSection, mixed ...$args): mixed
    {
        return parent::__invoke(function (mixed ...$args) use ($criticalSection) {
            if ($this->wrappedCriticalSection) {
                return ($this->wrappedCriticalSection)(static fn () => $criticalSection(...$args));
            }
            return $criticalSection(...$args);
        }, ...$args);
    }

    public function withLock(LockInterface $lock, bool|null $isBlocking = null): WrappingCriticalSection
    {
        return match ($isBlocking) {
            null => new Symfony\Lock\CriticalSection($lock, $this->getWrappingReferenceOrNull()),
            default => new Symfony\Lock\CriticalSection($lock, $this->getWrappingReferenceOrNull(), $isBlocking),
        };
    }

    /**
     * @param array<LockInterface> $locks
     */
    public function withLocks(array $locks, bool|null $isBlocking = null): WrappingCriticalSection
    {
        $locks = array_reverse($locks); // reverse array to keep order of locks during wrapping
        $instance = $this;
        foreach ($locks as $lock) {
            $instance = $instance->withLock($lock, $isBlocking);
        }
        return $instance;
    }

    protected function getWrappingReferenceOrNull(): CriticalSection|null
    {
        return $this;
    }
}
