<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

final class WrappingCriticalSectionTest extends CriticalSectionTestCase
{
    use CriticalSectionStaticFactory;

    public function testComposesCriticalSections(): void
    {
        $shared = new \stdClass();
        $shared->enters = [];
        $shared->leaves = [];

        $prepareSection = function (int $id, ?WrappingCriticalSection $outer) use ($shared) {
            return new class ($id, $outer, $shared) extends WrappingCriticalSection {
                public function __construct(
                    private readonly int $id,
                    ?WrappingCriticalSection $wrappedCriticalSection,
                    private readonly \stdClass $shared,
                ) {
                    parent::__construct($wrappedCriticalSection, false);
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

        $section3(static function () use ($shared) {
            self::assertSame([3, 2, 1], $shared->enters);
            self::assertSame([], $shared->leaves);
        });
        self::assertSame([1, 2, 3], $shared->leaves);
    }

    protected function createCriticalSection(bool $isBlocking): CriticalSection|null
    {
        return $isBlocking ? null : self::nonCritical(
            self::nonCritical(canEnter: true),
            canEnter: true,
        );
    }

    protected function createUnenterableCriticalSection(bool $isBlocking): CriticalSection|null
    {
        return $isBlocking ? null : self::nonCritical(
            self::nonCritical(
                self::nonCritical(canEnter: true),
                canEnter: false,
            ),
            canEnter: true,
        );
    }

    protected function createUnleavableCriticalSection(): CriticalSection|null
    {
        return null;
    }
}
