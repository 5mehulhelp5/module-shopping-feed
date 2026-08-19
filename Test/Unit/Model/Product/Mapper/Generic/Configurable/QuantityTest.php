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
 * Class QuantityTest
 *
 * @package MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Configurable
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class QuantityTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\Quantity
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

        $this->expectReturn($this->adapterMock, 'getInventoryCount', 100);
        $this->expectReturn($this->parentAdapterMock, 'getData', [$this->adapterMock]);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\Quantity',
            []
        );
    }

    public function testMap()
    {
        $this->model->addAdapter($this->parentAdapterMock);

        $params = [];
        $cell = $this->model->map($params);
        $this->assertEquals(100, $cell);
    }
}
