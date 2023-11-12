<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

/**
 * @template T
 *
 * @extends WrappingCriticalSection<T>
 */
final class NonCriticalSection extends WrappingCriticalSection
{
    /**
     * @internal Use {@link CriticalSection::create()}
     *
     * @param CriticalSectionInterface<T>|null $wrappedCriticalSection
     */
    public function __construct(
        private ?CriticalSectionInterface $wrappedCriticalSection = null,
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

    /** @return CriticalSectionInterface<T>|null */
    protected function getWrappingReferenceOrNull(): ?CriticalSectionInterface
    {
        return $this->wrappedCriticalSection;
    }
}
