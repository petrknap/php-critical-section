<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection;

use PHPUnit\Framework\TestCase;

abstract class CriticalSectionTestCase extends TestCase
{
    /**
     * @dataProvider isBlocking
     */
    public function testForwardsArgumentsIntoCriticalSection(bool $isBlocking): void
    {
        $expectedArgs = ['string', 1, null];
        $receivedArgs = [];
        self::skipOnNull($this->createCriticalSection($isBlocking))(
            function (string $s, int $i, mixed $n) use (&$receivedArgs): void {
                $receivedArgs = func_get_args();
            },
            ...$expectedArgs,
        );

        self::assertEquals(
            $expectedArgs,
            $receivedArgs,
        );
    }

    /**
     * @dataProvider isBlocking
     */
    public function testForwardsReturnFromCriticalSection(bool $isBlocking): void
    {
        $expectedReturn = new Some\Resource(1);

        self::assertSame(
            $expectedReturn,
            self::skipOnNull($this->createCriticalSection($isBlocking))(static fn (): Some\Resource => $expectedReturn),
        );
    }

    /**
     * @dataProvider isBlocking
     */
    public function testCouldNotEnterUnenterableSection(bool $isBlocking): void
    {
        if ($isBlocking) {
            self::expectException(Exception\CouldNotEnterCriticalSection::class);
        }
        self::assertNull(self::skipOnNull($this->createUnenterableCriticalSection($isBlocking))(static fn (): bool => true));
    }

    public function testCouldNotLeaveUnleavableSection(): void
    {
        self::expectException(Exception\CouldNotLeaveCriticalSection::class);
        self::skipOnNull($this->createUnleavableCriticalSection())(static fn (): bool => true);
    }

    public static function isBlocking(): array
    {
        return [
            'blocking' => [true],
            'non-blocking' => [false]
        ];
    }

    abstract protected function createCriticalSection(bool $isBlocking): CriticalSection|null;

    abstract protected function createUnenterableCriticalSection(bool $isBlocking): CriticalSection|null;

    abstract protected function createUnleavableCriticalSection(): CriticalSection|null;

    private static function skipOnNull(CriticalSection|null $criticalSection): CriticalSection
    {
        if ($criticalSection === null) {
            self::markTestSkipped('Irrelevant test');
        }
        return $criticalSection;
    }
}
