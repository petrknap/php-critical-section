<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use Throwable;

abstract class CriticalSection
{
    use CriticalSectionStaticFactory;

    protected function __construct(
        protected readonly bool $isBlocking,
    ) {
    }

    /**
     * @phpstan-ignore-next-line Template type T ... is not referenced in a parameter.
     *
     * @template T of mixed
     *
     * @param (callable(mixed ...$args): T)|callable $criticalSection
     * @param mixed ...$args will be forwarded to {@link $criticalSection}
     *
     * @return T|null returned by {@link $criticalSection} or null when it is occupied (non-blocking mode only)
     *
     * @throws Exception\CouldNotEnterCriticalSection
     * @throws Exception\CouldNotLeaveCriticalSection
     * @throws Throwable from {@link $criticalSection}
     */
    public function __invoke(callable $criticalSection, mixed ...$args): mixed
    {
        if ($this->enter() === false) {
            return null;
        }
        try {
            return $criticalSection(...$args);
        } finally {
            $this->leave();
        }
    }

    /**
     * @return bool false if it is occupied (non-blocking mode only)
     *
     * @throws Exception\CouldNotEnterCriticalSection
     */
    abstract protected function enter(): bool;

    /**
     * @throws Exception\CouldNotLeaveCriticalSection
     */
    abstract protected function leave(): void;
}
