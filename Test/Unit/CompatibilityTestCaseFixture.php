<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit;

class CompatibilityTestCaseFixture
{
    public function __call(string $name, array $arguments): mixed
    {
        return null;
    }
}
