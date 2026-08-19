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


namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Configurable;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class ShippingWeightTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ShippingWeightTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\ShippingWeight
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
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\ShippingWeight',
            []
        );
    }

    public function testMap()
    {
        $this->expectReturn($this->parentAdapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->parentAdapterMock, 'getData', [$this->adapterMock]);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->parentAdapterMock, 'getMapAttribute', 'weightAttribute');
        $this->expectReturn($this->parentAdapterMock, 'getAttributeValue', 123);

        $this->model->addAdapter($this->parentAdapterMock);

        $params = ['param' => 'g'];
        $cell = $this->model->map($params);
        $this->assertEquals('123.00 g', $cell);
    }
}
