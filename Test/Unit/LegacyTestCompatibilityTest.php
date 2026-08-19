<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit;

use MageOS\ShoppingFeed\Test\Unit\Model\Feed\Source\Product\OptionsHandlingTest;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class LegacyTestCompatibilityTest extends TestCase
{
    public function testSharedLocaleDateMockIsDeclared(): void
    {
        $this->assertTrue((new ReflectionClass(ModelFramework::class))->hasProperty('localeDateMock'));
    }

    public function testOptionHandlingSourceModelIsDeclared(): void
    {
        $this->assertTrue((new ReflectionClass(OptionsHandlingTest::class))->hasProperty('sourceMode'));
    }
}
