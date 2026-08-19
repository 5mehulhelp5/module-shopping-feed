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
class PriceTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Grouped\Price
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
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Grouped\Price',
            []
        );
    }

    public function testMapPriceSum()
    {
        $this->expectReturn($this->parentAdapterMock, 'getData', [$this->adapterMock]);
        $this->expectReturn(
            $this->adapterMock, 'getPrices', [
                'p_excl_tax' => 100,
                'p_incl_tax' => 122
            ]
        );
        $this->expectReturn($this->feedMock, 'getConfig', 1);
        $this->expectReturn($this->productMock, 'getQty', 1);

        $this->model->addAdapter($this->parentAdapterMock);

        $params = ['param' => true];
        $cell = $this->model->map($params);
        $this->assertEquals(122, $cell);

        $params = ['param' => false];
        $cell = $this->model->map($params);
        $this->assertEquals(100, $cell);
    }

    public function testMapStartAt()
    {
        $this->expectReturn($this->parentAdapterMock, 'getData', [$this->adapterMock]);
        $this->expectReturn(
            $this->adapterMock, 'getPrices', [
                'p_excl_tax' => 100,
                'p_incl_tax' => 122
            ]
        );
        $this->expectReturn($this->feedMock, 'getConfig', 0);

        $this->model->addAdapter($this->parentAdapterMock);

        $params = ['param' => true];
        $cell = $this->model->map($params);
        $this->assertEquals(122, $cell);

        $params = ['param' => false];
        $cell = $this->model->map($params);
        $this->assertEquals(100, $cell);
    }

    public function testMapStartAtNoAssociated()
    {
        $this->expectReturn($this->parentAdapterMock, 'getData', []);
        $this->expectReturn($this->parentAdapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->feedMock, 'getConfig', 0);

        $this->model->addAdapter($this->parentAdapterMock);

        $params = ['param' => true];
        $cell = $this->model->map($params);
        $this->assertEquals(0, $cell);
    }

    public function testMapEmpty()
    {
        $this->expectReturn($this->parentAdapterMock, 'getData', []);
        $this->expectReturn($this->parentAdapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->feedMock, 'getConfig', 3);

        $this->model->addAdapter($this->parentAdapterMock);

        $params = ['param' => true];
        $cell = $this->model->map($params);
        $this->assertEquals(0, $cell);
    }
}
