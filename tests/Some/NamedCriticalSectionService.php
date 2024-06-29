<?php

declare(strict_types=1);

namespace PetrKnap\CriticalSection\Some;

use PetrKnap\CriticalSection\LockableResource;
use PetrKnap\CriticalSection\Locked;
use PetrKnap\CriticalSection\Symfony\Lock\LockPoolingHelper;
use Symfony\Component\Lock\LockFactory;

final class NamedCriticalSectionService
{
    public function __construct(
        private readonly LockFactory $lockFactory,
    ) {
    }

    /**
     * @template T of mixed
     *
     * @param callable(Locked<Resource> ...$resources): T $do
     *
     * @return T
     */
    public function updateSomeResources(callable $do, Resource ...$resources): mixed
    {
        $lockPool = LockPoolingHelper::createLockPool();
        foreach ($resources as &$resource) {
            $resource = LockableResource::of(
                $resource,
                LockPoolingHelper::getOrCreateLock($lockPool, $this->lockFactory, 'some_resource-' . $resource->id),
            );
        }
        return LockPoolingHelper::createCriticalSection($lockPool)($do, ...$resources);
    }
}
