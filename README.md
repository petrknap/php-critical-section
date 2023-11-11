# Critical section based on `symfony/lock`

The [CriticalSection](./src/CriticalSection.php) is a simple object that handles the lock manipulation for you
and lets you focus on the actual code.

```php
use PetrKnap\CriticalSection\CriticalSection;
use Symfony\Component\Lock\NoLock;

$lock = new NoLock();
var_dump(
    CriticalSection::withLock($lock)(function() {
        return 'This was critical!';
    })
);
```

---

Run `composer require petrknap/critical-section` to install it.
You can [support this project via donation](https://petrknap.github.io/donate.html).
The project is licensed under [the terms of the `LGPL-3.0-or-later`](./COPYING.LESSER).
