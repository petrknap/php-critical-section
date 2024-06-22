<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection\Symfony\Lock;

use PetrKnap\CriticalSection\LockedResource as Base;
use Symfony\Component\Lock\LockInterface;

/**
 * @template T of mixed
 *
 * @extends Base<T>
 */
final class LockedResource extends Base
{
    /**
     * @var array<LockInterface>
     */
    private readonly array $locks;

    /**
     * @param T $resource
     */
    public function __construct(
        mixed $resource,
        LockInterface $lock1,
        LockInterface ...$lockN,
    ) {
        parent::__construct($resource);
        $this->locks = [$lock1, ...$lockN];
    }

    protected function isLocked(): bool
    {
        foreach ($this->locks as $lock) {
            if (!$lock->isAcquired()) {
                return false;
            }
        }
        return true;
    }
}
