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


namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Formatter\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class CurrencyTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CurrencyTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Formatter\Price\Currency
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

        $this->expectReturn($this->adapterMock, 'getStore', $this->storeMock);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Formatter\Price\Currency',
            []
        );
    }

    public function testRun()
    {
        $this->expectAdvencedReturn(
            $this->adapterMock,
            'getData',
            $this->returnCallback(function ($key, $default = '') {
                switch ($key) {
                    case 'store_currency_code':
                        return 'USD';
                    default:
                        return $default;
                }
            })
        );

        $this->model->setAdapter($this->adapterMock);
        $this->assertInstanceOf(
            'MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple',
            $this->model->getAdapter()
        );

        $this->assertEmpty($this->model->run('something'));
        $this->assertEmpty($this->model->run(-1));
        $this->assertEmpty($this->model->run(0));
        $this->assertEquals('2.43 USD', $this->model->run(2.432));
    }
}
