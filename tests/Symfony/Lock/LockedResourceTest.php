<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection\Symfony\Lock;

use PetrKnap\CriticalSection\LockedResourceTestCase;
use Symfony\Component\Lock\LockInterface;

final class LockedResourceTest extends LockedResourceTestCase
{
    protected function getUnlockedResource(mixed $resource): LockedResource
    {
        $lock = $this->createMock(LockInterface::class);
        $lock->expects(self::any())->method('isAcquired')->willReturn(false);
        return new LockedResource($resource, $lock);
    }

    protected function getLockedResource(mixed $resource): LockedResource
    {
        $lock = $this->createMock(LockInterface::class);
        $lock->expects(self::any())->method('isAcquired')->willReturn(true);
        return new LockedResource($resource, $lock);
    }
}
