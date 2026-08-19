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


namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Simple;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class FeedTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class AvailabilityTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Id
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;


    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Availability',
            ['status' => $this->statusMock]
        );
    }

    public function testMapDefaultStock()
    {
        $this->expectReturn($this->feedMock, 'getConfig', true);
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn($this->statusMock, 'getStockStatus', 1);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);

        $this->model->addAdapter($this->adapterMock);

        $params = [];
        $cell = $this->model->map($params);
        $this->assertEquals('in_stock', $cell);
    }
    public function testMapDefaultOutOfStock()
    {
        $this->expectReturn($this->feedMock, 'getConfig', true);
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn($this->statusMock, 'getStockStatus', 0);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);

        $this->model->addAdapter($this->adapterMock);

        $params = [];
        $cell = $this->model->map($params);
        $this->assertEquals('out_of_stock', $cell);
    }

    public function testMapAttributeStockInStock()
    {
        $this->expectAdvencedReturn(
            $this->feedMock,
            'getConfig',
            $this->returnCallback(static function ($key) {
                return $key === 'general_stock_attribute_code' ? 'custom_attribute' : 0;
            })
        );
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn($this->statusMock, 'getStockStatus', 0);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->adapterMock, 'getMapAttribute', 'attribute_as_string');
        $this->expectReturn($this->adapterMock, 'getAttributeValue', 'in stock');

        $this->model->addAdapter($this->adapterMock);

        $params = [];
        $cell = $this->model->map($params);
        $this->assertEquals('in_stock', $cell);
    }

    public function testMapAttributeStockOutOfStock()
    {
        $this->expectAdvencedReturn(
            $this->feedMock,
            'getConfig',
            $this->returnCallback(static function ($key) {
                return $key === 'general_stock_attribute_code' ? 'custom_attribute' : 0;
            })
        );
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn($this->statusMock, 'getStockStatus', 0);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->adapterMock, 'getMapAttribute', 'attribute_as_string');
        $this->expectReturn($this->adapterMock, 'getAttributeValue', 'garbage');

        $this->model->addAdapter($this->adapterMock);

        $params = [];
        $cell = $this->model->map($params);
        $this->assertEquals('out_of_stock', $cell);
    }

    public function testFilter()
    {
        $this->expectReturn($this->feedMock, 'getConfig', false);
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);

        $this->model->addAdapter($this->adapterMock);

        $this->assertEquals(true, $this->model->filter('out_of_stock'));
        $this->assertEquals(false, $this->model->filter('in_stock'));
    }
}
