<?php
/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   MageOS_ShoppingFeed
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\LocalInventory\Configurable\Associated;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class FeedTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class AvailabilityTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\LocalInventory\Configurable\Associated\Availability
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->parentAdapterMock = clone $this->adapterMock;

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\LocalInventory\Configurable\Associated\Availability',
            ['status' => $this->statusMock]
        );
    }

    /** @dataProvider parentStockProvider */
    #[\PHPUnit\Framework\Attributes\DataProvider('parentStockProvider')]
    public function testParentStockUsesSalabilityWithoutRequiringParentQuantity(
        bool $inherit,
        bool $defaultStock,
        bool $parentSalable,
        string $parentStock,
        string $childStock,
        string $expected
    ): void {
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->adapterMock = $this->getModelMock(
            \MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple::class,
            ['getParentAdapter', 'getFeed', 'getFilter']
        );
        $this->expectReturn($this->adapterMock, 'getFilter', $this->filterMock);
        $model = $this->getMockBuilder(
            \MageOS\ShoppingFeed\Model\Product\Mapper\LocalInventory\Configurable\Associated\Availability::class
        )->disableOriginalConstructor()->onlyMethods(['getStockStatus', 'usesDefaultStock'])->getMock();
        $model->method('usesDefaultStock')->willReturn($defaultStock);
        $this->expectReturn($this->feedMock, 'getConfig', $inherit);
        $this->expectReturn($this->productMock, 'isSalable', $parentSalable);
        $this->expectReturn($this->parentAdapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->adapterMock, 'getParentAdapter', $this->parentAdapterMock);
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $model->method('getStockStatus')->willReturnCallback(
            fn($adapter) => $adapter === $this->parentAdapterMock ? $parentStock : $childStock
        );
        $model->addAdapter($this->adapterMock);

        $this->assertSame($expected, $model->map());
    }

    public static function parentStockProvider(): array
    {
        return [
            'salable parent with zero quantity' => [true, true, true, 'out_of_stock', 'in_stock', 'in_stock'],
            'unavailable parent' => [true, true, false, 'in_stock', 'in_stock', 'out_of_stock'],
            'unavailable child' => [true, true, true, 'out_of_stock', 'out_of_stock', 'out_of_stock'],
            'parent inheritance disabled' => [false, true, false, 'out_of_stock', 'in_stock', 'in_stock'],
            'custom parent stock attribute' => [true, false, true, 'out_of_stock', 'in_stock', 'out_of_stock'],
        ];
    }
}
