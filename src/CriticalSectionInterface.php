<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use PetrKnap\CriticalSection\Exception\CouldNotEnterCriticalSection;
use PetrKnap\CriticalSection\Exception\CouldNotLeaveCriticalSection;
use Throwable;

/**
 * @template T of mixed
 */
interface CriticalSectionInterface
{
    /**
     * @param (callable(mixed...): T)|callable $criticalSection
     * @param mixed ...$args will be forwarded to {@link $criticalSection}
     *
     * @return T|null returned by {@link $criticalSection} or null when it is occupied (non-blocking mode only)
     *
     * @throws CouldNotEnterCriticalSection
     * @throws CouldNotLeaveCriticalSection
     * @throws Throwable from {@link $criticalSection}
     */
    public function __invoke(callable $criticalSection, mixed ...$args);
}
