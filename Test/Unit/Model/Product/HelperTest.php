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


namespace MageOS\ShoppingFeed\Test\Unit\Model\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\CompatibilityTestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class HelperTest extends CompatibilityTestCase
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Helper
     */
    protected $model;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryMock;

    /**
     * @var \Magento\Msrp\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $msrpMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $feedMock;
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->stockRegistryMock = $this->createCompatibleMock(
            'Magento\CatalogInventory\Model\StockRegistry',
            ['getStockItem', 'getMinSaleQty', 'getQtyIncrements']
        );

        $this->msrpMock = $this->createCompatibleMock(
            '\Magento\Msrp\Model\Config',
            ['isEnabled']
        );

        $this->productMock = $this->createCompatibleMock(
            '\Magento\Catalog\Model\Product',
            ['getId', 'getPrice', 'getMsrp', 'hasMsrp']
        );

        $this->feedMock = $this->createCompatibleMock(
            'MageOS\ShoppingFeed\Model\Feed',
            ['getId', 'getConfig']
        );
        $this->feedMock->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue(true));


        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Helper',
            [
                'msrp' => $this->msrpMock,
                'stockRegistry' => $this->stockRegistryMock
            ]
        );
    }

    public function testGetQuantityIncrements()
    {
        $this->stockRegistryMock->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnSelf());
        $this->stockRegistryMock->expects($this->any())
            ->method('getMinSaleQty')
            ->will($this->returnValue(3));
        $this->stockRegistryMock->expects($this->any())
            ->method('getQtyIncrements')
            ->will($this->returnValue(2));

        $this->assertEquals(4, $this->model->getQuantityIcrements($this->productMock, $this->feedMock));
    }

    public function testGetQuantityIncrementsWtihoutMinSaleQty()
    {
        $this->stockRegistryMock->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnSelf());
        $this->stockRegistryMock->expects($this->any())
            ->method('getMinSaleQty')
            ->will($this->returnValue(null));
        $this->stockRegistryMock->expects($this->any())
            ->method('getQtyIncrements')
            ->will($this->returnValue(2));

        $this->assertEquals(2, $this->model->getQuantityIcrements($this->productMock, $this->feedMock));
    }

    public function testHasMsrp()
    {
        $this->msrpMock->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true));

        $this->productMock->expects($this->any())
            ->method('hasMsrp')
            ->will($this->returnValue(true));
        $this->productMock->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue(10));
        $this->productMock->expects($this->any())
            ->method('getMsrp')
            ->will($this->returnValue(20));

        $this->assertEquals(true, $this->model->hasMsrp($this->productMock));
    }
}
