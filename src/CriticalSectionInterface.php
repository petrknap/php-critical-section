<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use PetrKnap\CriticalSection\Exception\CouldNotEnterCriticalSection;
use PetrKnap\CriticalSection\Exception\CouldNotLeaveCriticalSection;
use Throwable;

/**
 * @template T
 */
interface CriticalSectionInterface
{
    /**
     * @param callable(): T $criticalSection
     *
     * @return ?T returned by {@link $criticalSection} or null when it is occupied (non-blocking mode only)
     *
     * @throws CouldNotEnterCriticalSection
     * @throws CouldNotLeaveCriticalSection
     * @throws Throwable from {@link $criticalSection}
     */
    public function __invoke(callable $criticalSection);
}
