<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CompatibilityTestCaseTest extends CompatibilityTestCase
{
    public function testVirtualMagentoDataMethodCanBeMocked(): void
    {
        $mock = $this->createCompatibleMock(
            CompatibilityTestCaseFixture::class,
            ['getId', 'setId']
        );
        $mock->expects($this->once())->method('getId')->will(self::returnValue(42));
        $mock->expects($this->once())->method('setId')->with(42)->will(self::returnSelf());

        $this->assertSame(42, $mock->getId());
        $this->assertSame($mock, $mock->setId(42));
    }
}
