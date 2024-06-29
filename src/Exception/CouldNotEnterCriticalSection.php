<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection\Exception;

use PetrKnap\Shorts\ExceptionWrapper;
use RuntimeException;

final class CouldNotEnterCriticalSection extends RuntimeException implements CriticalSectionException
{
    use ExceptionWrapper;
}
