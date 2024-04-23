<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockInterface;

final class WrappingCriticalSectionTest extends TestCase
{
    private const FOO = 'bar';

    public function testReturnsValueReturnedByExecutedCriticalSection(): void
    {
        self::assertSame(
            self::FOO,
            (new NonCriticalSection(canEnter: true))(fn () => self::FOO)
        );
    }

    public function testSkipsCriticalSectionWhenItIsOccupiedInNonBlockingMode(): void
    {
        self::assertNull((new NonCriticalSection(canEnter: false))(fn () => self::FOO));
    }

    public function testComposesCriticalSections(): void
    {
        $shared = new \stdClass();
        $shared->enters = [];
        $shared->leaves = [];

        $prepareSection = function (int $id, ?WrappingCriticalSection $outer) use ($shared) {
            return new class ($id, $outer, $shared) extends WrappingCriticalSection {
                public function __construct(
                    private int $id,
                    ?WrappingCriticalSection $wrappedCriticalSection,
                    private \stdClass $shared,
                ) {
                    parent::__construct($wrappedCriticalSection);
                }

                public function enter(): bool
                {
                    $this->shared->enters[] = $this->id;
                    return true;
                }

                public function leave(): void
                {
                    $this->shared->leaves[] = $this->id;
                }
            };
        };

        $section1 = $prepareSection(1, null);
        $section2 = $prepareSection(2, $section1);
        $section3 = $prepareSection(3, $section2);

        $section3(function () use ($shared) {
            self::assertSame([3, 2, 1], $shared->enters);
            self::assertSame([], $shared->leaves);
        });
        self::assertSame([1, 2, 3], $shared->leaves);
    }
}
