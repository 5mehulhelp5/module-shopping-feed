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
 * Class PriceTest
 *
 * @package MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Simple
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class PriceTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Price
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->expectReturn($this->adapterMock, 'getData', 'USD');

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Price',
            []
        );
    }

    public function testMap()
    {
        $this->expectReturn(
            $this->adapterMock, 'getPrices', [
                'p_excl_tax' => 100,
                'p_incl_tax' => 122
            ]
        );

        $this->model->addAdapter($this->adapterMock);

        $params = ['param' => true];
        $cell = $this->model->map($params);
        $this->assertEquals(122, $cell);

        $params = ['param' => false];
        $cell = $this->model->map($params);
        $this->assertEquals(100, $cell);
    }

    public function testFilterTrue()
    {
        $this->expectAdvencedReturn($this->feedMock, 'getConfig', $this->onConsecutiveCalls(100, 50));
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);

        $this->model->addAdapter($this->adapterMock);

        $this->assertEquals(true, $this->model->filter(10));
    }

    public function testFilterFalse()
    {
        $this->expectAdvencedReturn($this->feedMock, 'getConfig', $this->onConsecutiveCalls(100, 50));
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);

        $this->model->addAdapter($this->adapterMock);

        $this->assertEquals(false, $this->model->filter(70));
    }
}
