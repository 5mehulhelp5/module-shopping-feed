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

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Grouped;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class PriceTest
 *
 * @package MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Grouped
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class SalePriceTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Grouped\SalePrice
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
        $this->expectReturn($this->parentAdapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->adapterMock);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Grouped\SalePrice',
            []
        );
    }

    public function testMapHasSpecialPrice()
    {
        $this->expectReturn($this->parentAdapterMock, 'hasSpecialPrice', false);

        $this->model->addAdapter($this->parentAdapterMock);

        $params = ['param' => false];
        $cell = $this->model->map($params);
        $this->assertEquals('', $cell);
    }

    public function testMapSalePriceSum()
    {
        $this->expectReturn($this->parentAdapterMock, 'hasSpecialPrice', true);
        $this->expectReturn($this->parentAdapterMock, 'getData', [$this->adapterMock]);
        $this->expectReturn(
            $this->adapterMock, 'getPrices', [
                'p_excl_tax' => 100,
                'p_incl_tax' => 122,
                'sp_excl_tax' => 50,
                'sp_incl_tax' => 61
            ]
        );
        $this->expectReturn($this->feedMock, 'getConfig', 1);
        $this->expectReturn($this->productMock, 'getQty', 1);

        $this->model->addAdapter($this->parentAdapterMock);

        $params = ['param' => true];
        $cell = $this->model->map($params);
        $this->assertEquals(61, $cell);

        $params = ['param' => false];
        $cell = $this->model->map($params);
        $this->assertEquals(50, $cell);
    }
}
