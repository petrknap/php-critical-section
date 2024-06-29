<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use PetrKnap\Shorts\Exception\NotImplemented;
use Symfony\Component\Lock\LockInterface;

/**
 * @internal please use {@see CriticalSection}
 *
 * @method static WrappingCriticalSection withLock(LockInterface $lock, bool|null $isBlocking = null)
 * @method static WrappingCriticalSection withLocks(array $locks, bool|null $isBlocking = null)
 */
trait CriticalSectionStaticFactory
{
    /**
     * @todo refactor it when {@see https://bugs.php.net/bug.php?id=40837} will be fixed
     *
     * @param array<mixed> $arguments
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return match ($name) {
            'withLock', 'withLocks' => self::nonCritical(canEnter: true)->$name(...$arguments),
            default => NotImplemented::throw("Static method `{$name}`"),
        };
    }

    private static function nonCritical(CriticalSection $wrappedCriticalSection = null, bool $canEnter = null): WrappingCriticalSection
    {
        return new class ($wrappedCriticalSection, (bool) $canEnter) extends WrappingCriticalSection {
            public function __construct(
                private readonly CriticalSection|null $wrappedCriticalSection,
                private readonly bool $canEnter,
            ) {
                parent::__construct($wrappedCriticalSection, false);
            }

            public function enter(): bool
            {
                return $this->canEnter;
            }

            public function leave(): void
            {
            }

            protected function getWrappingReferenceOrNull(): CriticalSection|null
            {
                return $this->wrappedCriticalSection;
            }
        };
    }
}
