<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockInterface;

final class AliasesTest extends TestCase
{
    public function testLockedResourceAliases(): void
    {
        self::assertInstanceOf(
            Locked::class,
            LockableResource::of(
                new Some\Resource(),
                self::createStub(LockInterface::class),
            ),
        );
    }
}
