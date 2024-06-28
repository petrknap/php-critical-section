<?php

/**
 * Aliases for better code readability
 */

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

class_alias(LockedResource::class, 'PetrKnap\CriticalSection\LockableResource');
class_alias(LockedResource::class, 'PetrKnap\CriticalSection\Locked');
