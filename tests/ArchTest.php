<?php

declare(strict_types=1);

it('doesn\'t use dd, dump, or ray')
    ->expect(['dd', 'dump', 'ray'])
    ->each
    ->not
    ->toBeUsed();
