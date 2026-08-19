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
 * Class ShippingTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ShippingTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Id
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->productMock, 'getId', 1);
        $this->expectAdvencedReturn(
            $this->feedMock,
            'getConfig',
            $this->returnCallback(function ($arg) {
                switch ($arg) {
                    case 'shipping_country':
                        return ['USA'];
                    default:
                        return '';
                }
            })
        );

        $this->expectSelf($this->shippingProviderMock, 'prepareCache');

        // Set up null cache in shipping Mock, used in ;
        //$this->expectReturn($this->cacheMock, 'getCache', null);

        $shippingMock = $this->getModelMock('MageOS\ShoppingFeed\Model\Product\Shipping',
            ['setRequest', 'collectRates', 'getFormatedValue', 'getProductWeight']);

        $this->expectReturn($shippingMock, 'getFormatedValue', 'shipping value');
        $this->expectReturn($shippingMock, 'setRequest',
            $this->getModelMock('Magento\Quote\Model\Quote\Address\RateRequest'));
        $this->expectReturn($shippingMock, 'getProductWeight', 1);
        $this->expectReturn($this->shippingProviderMock, 'getShipping', $shippingMock);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Shipping',
            [
                'cache' => $this->cacheMock,
                'shippingProvider' => $this->shippingProviderMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
        $this->model->addAdapter($this->adapterMock);
    }

    public function testMap()
    {
        $this->expectReturn($this->cacheMock, 'getCache', false);
        $this->expectReturn($this->shippingProviderMock, 'getCache', false);

        $this->assertEquals('shipping value', $this->model->map());
    }

    public function testMapInterfaceCache()
    {
        $this->expectReturn($this->cacheMock, 'getCache', false);
        $this->expectReturn($this->shippingProviderMock, 'getCache', 'cache value');

        $this->assertEquals('cache value', $this->model->map());
    }

    public function testMapCache()
    {
        $this->expectReturn($this->cacheMock, 'getCache', 'cache value');

        $this->assertEquals('cache value', $this->model->map());
    }
}
