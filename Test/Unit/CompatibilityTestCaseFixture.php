<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit;

/**
 * Stands in for a Magento DataObject whose accessors are virtual and served by __call().
 *
 * @method mixed getId()
 * @method mixed setId(mixed $id)
 */
class CompatibilityTestCaseFixture
{
    public function __call(string $name, array $arguments): mixed
    {
        return null;
    }
}
