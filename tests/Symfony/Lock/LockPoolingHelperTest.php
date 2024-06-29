<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection\Symfony\Lock;

use PetrKnap\Optional\OptionalBool;
use PetrKnap\Optional\OptionalFloat;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\SharedLockInterface;

class LockPoolingHelperTest extends TestCase
{
    private const RESOURCE = 'resource';

    private MockObject&SharedLockInterface $lock;
    private MockObject&LockFactory $lockFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->lock = self::createMock(SharedLockInterface::class);
        $this->lockFactory = self::createMock(LockFactory::class);
    }

    public function testGetsLockWhenLockIsPresentInPool(): void
    {
        $this->lockFactory
            ->expects(self::never())
            ->method('createLock')
        ;
        $lockPool = [self::RESOURCE => $this->lock];

        self::assertSame(
            $this->lock,
            LockPoolingHelper::getOrCreateLock($lockPool, $this->lockFactory, self::RESOURCE),
        );
    }

    /**
     * @dataProvider dataCreatesNewLockAndAddsItToPoolWhenLockIsNotPresentInPool
     */
    public function testCreatesNewLockAndAddsItToPoolWhenLockIsNotPresentInPool(OptionalFloat|null $ttl, OptionalBool|null $autoRelease): void
    {
        $this->lockFactory
            ->expects(self::once())
            ->method('createLock')
            ->with(self::RESOURCE, $ttl === null ? 300.0 : null, $autoRelease === null ? true : false)
            ->willReturn($this->lock)
        ;
        $lockPool = [];

        self::assertSame(
            $this->lock,
            LockPoolingHelper::getOrCreateLock($lockPool, $this->lockFactory, self::RESOURCE, $ttl, $autoRelease),
        );

        self::assertSame(
            [self::RESOURCE => $this->lock],
            $lockPool,
        );
    }

    public static function dataCreatesNewLockAndAddsItToPoolWhenLockIsNotPresentInPool(): array
    {
        return [
            'ttl & autoRelease' => [OptionalFloat::empty(), OptionalBool::of(false)],
            'ttl' => [OptionalFloat::empty(), null],
            'autoRelease' => [null, OptionalBool::of(false)],
            'default values' => [null, null],
        ];
    }

    public function testCreatesCriticalSectionWithSortedLocksFromPool(): void
    {
        $acquired = [];
        $lockA = self::createMock(LockInterface::class);
        $lockA->method('acquire')->willReturnCallback(static function () use (&$acquired) {
            $acquired[] = 'a';
            return true;
        });
        $lockB = self::createMock(LockInterface::class);
        $lockB->method('acquire')->willReturnCallback(static function () use (&$acquired) {
            $acquired[] = 'b';
            return true;
        });
        $lockC = self::createMock(LockInterface::class);
        $lockC->method('acquire')->willReturnCallback(static function () use (&$acquired) {
            $acquired[] = 'c';
            return true;
        });
        $lockPool = [
            'b' => $lockB,
            'a' => $lockA,
            'c' => $lockC,
        ];

        LockPoolingHelper::createCriticalSection($lockPool)(static fn (): bool => true);

        self::assertSame(
            ['a', 'b', 'c'],
            $acquired,
        );
    }
}
