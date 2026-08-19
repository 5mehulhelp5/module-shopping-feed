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
 * Class ShippingWeightTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ShippingWeightTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\ShippingWeight
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\ShippingWeight',
            []
        );
    }

    public function testMap()
    {
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->adapterMock, 'getMapAttribute', 'weightAttribute');
        $this->expectReturn($this->adapterMock, 'getAttributeValue', 123);

        $this->model->addAdapter($this->adapterMock);

        $params = ['param' => 'g'];
        $cell = $this->model->map($params);
        $this->assertEquals('123.00 g', $cell);
    }

    public function testMapException()
    {
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->adapterMock, 'getMapAttribute', false);

        $this->model->addAdapter($this->adapterMock);

        $this->setExpectedException(
            'MageOS\ShoppingFeed\Model\Exception',
            'Couldn\'t find attribute \'weight\'.'
        );

        $params = ['param' => 'g'];
        $cell = $this->model->map($params);
        $this->assertEquals('123.00 g', $cell);
    }
}
