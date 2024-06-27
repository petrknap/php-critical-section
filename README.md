# Critical section based on `symfony/lock`

The [CriticalSection](./src/CriticalSection.php) is a simple object that handles the critical section overhead for you
and lets you focus on the actual code.

```php
use PetrKnap\CriticalSection\CriticalSection;
use Symfony\Component\Lock\NoLock;

$lock = new NoLock();

$criticalOutput = CriticalSection::withLock($lock)(fn () => 'This was critical.');

var_dump($criticalOutput);
```

You can wrap critical sections one inside the other thanks to the [WrappingCriticalSection](./src/WrappingCriticalSection.php).
This makes it easy to combine multiple locks, for example.

```php
use PetrKnap\CriticalSection\CriticalSection;
use Symfony\Component\Lock\NoLock;

$lockA = new NoLock();
$lockB = new NoLock();

$criticalOutput = CriticalSection::withLock($lockA)->withLock($lockB)(fn () => 'This was critical.');

var_dump($criticalOutput);
```

You can also pass locks as array and leave the composition to the critical section.

```php
use PetrKnap\CriticalSection\CriticalSection;
use Symfony\Component\Lock\NoLock;

$lockA = new NoLock();
$lockB = new NoLock();

$criticalOutput = CriticalSection::withLocks([$lockA, $lockB])(fn () => 'This was critical.');

var_dump($criticalOutput);
```

## Do you need to accept only locked resources?

Use [`LockedResource`s](./src/LockedResource.php) if you need to be sure that you are not processing resource outside it's critical section.

```php
namespace PetrKnap\CriticalSection;

use Symfony\Component\Lock\NoLock;

/** @param LockedResource<Example\Resource> $lockedResource */
function f(LockedResource $lockedResource) {
    echo $lockedResource->value;
}

$lock = new NoLock();
$resource = LockableResource::create(new Example\Resource('data'), $lock);
CriticalSection::withLock($lock)(fn () => f($resource));
```

## Does your critical section work with database?

Use [`doctrine/dbal`](https://packagist.org/packages/doctrine/dbal) and its `transactional` method.

```php
/** @var PetrKnap\CriticalSection\CriticalSectionInterface $criticalSection */
/** @var Doctrine\DBAL\Connection $connection */
$criticalSection(
    fn () => $connection->transactional(
        fn () => 'This was critical on DB server.'
    )
);
```

Always use `transactional` inside critical section to prevent starvation.

---

Run `composer require petrknap/critical-section` to install it.
You can [support this project via donation](https://petrknap.github.io/donate.html).
The project is licensed under [the terms of the `LGPL-3.0-or-later`](./COPYING.LESSER).
