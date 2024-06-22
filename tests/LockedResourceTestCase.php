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
        $string = 'string';
        /** @var LockedResource<Example\Resource> $locked */
        $locked = $this->getLockedResource(new Example\Resource(''));

        $locked->value = $string;
        self::assertSame($string, $locked->value);
        self::assertSame($string, $locked->getValue());
    }

    abstract protected function getUnlockedResource(mixed $resource): LockedResource;
    abstract protected function getLockedResource(mixed $resource): LockedResource;
}
