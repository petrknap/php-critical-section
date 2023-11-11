<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

/**
 * @internal
 *
 * @template T
 *
 * @extends WrappingCriticalSection<T>
 */
final class NonCriticalSection extends WrappingCriticalSection
{
    /** @param CriticalSectionInterface<T>|null $wrappedCriticalSection */
    public function __construct(
        ?CriticalSectionInterface $wrappedCriticalSection = null,
        private bool $canEnter = true,
    ) {
        parent::__construct($wrappedCriticalSection);
    }

    protected function enter(): bool
    {
        return $this->canEnter;
    }

    protected function leave(): void
    {
    }
}
