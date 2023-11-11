<?php declare(strict_types=1);

namespace PetrKnap\CriticalSection\Exception;

use RuntimeException;

final class CouldNotLeaveCriticalSection extends RuntimeException implements CriticalSectionException
{
}
