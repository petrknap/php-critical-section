<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use PHPUnit\Framework\TestCase;

final class NonCriticalSectionTest extends TestCase
{
    /** @dataProvider dataEntersCriticalSectionWhenCanEnter */
    public function testEntersCriticalSectionWhenCanEnter(bool $canEnter): void
    {
        $section = new NonCriticalSection(canEnter: $canEnter);

        if (
            $section(function () use ($canEnter) {
                self::assertTrue($canEnter);
                return true;
            }) === null
        ) {
            self::assertFalse($canEnter);
        }
    }

    public static function dataEntersCriticalSectionWhenCanEnter(): array
    {
        return [
            'yes' => [true],
            'no' => [false],
        ];
    }

    public function testDoesNotUseSelfAsWrappingReference(): void
    {
        self::markTestIncomplete();
    }
}
