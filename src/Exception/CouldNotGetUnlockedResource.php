<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection\Exception;

use PetrKnap\Shorts\Exception\CouldNotProcessData;

/**
 * @template T of mixed
 *
 * @extends CouldNotProcessData<T>
 */
final class CouldNotGetUnlockedResource extends CouldNotProcessData implements LockedResourceException
{
}
