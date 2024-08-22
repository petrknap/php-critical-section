<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use PHPUnit\Framework\TestCase;

abstract class LockedResourceTestCase extends TestCase
{
    public function testCouldNotGetUnlockedResource(): void
    {
        self::expectException(Exception\CouldNotGetUnlockedResource::class);

        $this->getUnlockedResource($this)->get();
    }

    public function testCouldGetLockedResource(): void
    {
        self::assertSame(
            $this,
            $this->getLockedResource($this)->get(),
        );
    }

    public function testIsMixin(): void
    {
        $value = 2;
        /** @var Locked<Some\Resource> $locked */
        $locked = $this->getLockedResource(new Some\Resource(1));

        $locked->value = $value;
        self::assertSame($value, $locked->value);
        self::assertSame($value, $locked->getValue());
    }

    abstract protected function getUnlockedResource(mixed $resource): LockedResource;
    abstract protected function getLockedResource(mixed $resource): LockedResource;
}
