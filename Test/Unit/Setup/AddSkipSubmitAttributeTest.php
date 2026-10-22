<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Setup;

use MageOS\ShoppingFeed\Setup\Patch\Data\AddSkipSubmitAttribute;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class AddSkipSubmitAttributeTest extends TestCase
{
    public function testCreatesAnEditableBooleanForEveryProductAttributeSet(): void
    {
        $setup = $this->createMock(EavSetup::class);
        $setup->method('getAttributeId')->with(Product::ENTITY, 'mageos_shopping_feed_skip_submit')
            ->willReturn(false);
        $setup->expects($this->once())->method('addAttribute')
            ->with(Product::ENTITY, 'mageos_shopping_feed_skip_submit', $this->callback(function (array $data): bool {
                self::assertSame('int', $data['type']);
                self::assertSame('boolean', $data['input']);
                self::assertSame(Boolean::class, $data['source']);
                self::assertSame(0, $data['default']);
                self::assertFalse($data['required']);
                self::assertTrue($data['visible']);
                self::assertSame('Skip from Being Submitted', $data['label']);
                self::assertSame(ScopedAttributeInterface::SCOPE_STORE, $data['global']);
                self::assertSame('Shopping Feed', $data['group']);
                self::assertTrue($data['user_defined'], 'Merchants must be able to manage the field.');
                self::assertSame(100, $data['sort_order']);
                return true;
            }));

        $patch = $this->patch($setup);
        self::assertSame($patch, $patch->apply());
        self::assertSame([], $patch->getAliases());
        self::assertSame([], AddSkipSubmitAttribute::getDependencies());
    }

    public function testPreservesAnAttributeAlreadyCreatedByAMerchant(): void
    {
        $setup = $this->createMock(EavSetup::class);
        $setup->method('getAttributeId')->willReturn(123);
        $setup->expects($this->never())->method('addAttribute');
        $setup->expects($this->never())->method('updateAttribute');
        $patch = $this->patch($setup);
        self::assertSame($patch, $patch->apply());
    }

    public function testEndsSetupWhenAttributeCreationFails(): void
    {
        $setup = $this->createMock(EavSetup::class);
        $setup->method('getAttributeId')->willReturn(false);
        $setup->method('addAttribute')->willThrowException(new \RuntimeException('fixture failure'));
        $patch = $this->patch($setup);
        $this->expectExceptionMessage('fixture failure');
        $patch->apply();
    }

    private function patch(EavSetup $setup): AddSkipSubmitAttribute
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())->method('startSetup');
        $connection->expects($this->once())->method('endSetup');
        $moduleSetup = $this->createMock(ModuleDataSetupInterface::class);
        $moduleSetup->method('getConnection')->willReturn($connection);
        $factory = $this->createMock(EavSetupFactory::class);
        $factory->method('create')->with(['setup' => $moduleSetup])->willReturn($setup);
        return new AddSkipSubmitAttribute($moduleSetup, $factory);
    }
}
