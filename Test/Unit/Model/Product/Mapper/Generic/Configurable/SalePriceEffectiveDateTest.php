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
 * Class SalePriceEffectiveDateTest
 *
 * @package MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Configurable
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class SalePriceEffectiveDateTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\SalePriceEffectiveDate
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


        $dateMock = new \DateTime('@1451649600');
        $dateMock->setTimezone(new \DateTimeZone('America/Chicago'));

        $this->expectReturn(
            $this->adapterMock, 'getSalePriceEffectiveDates', [
                'start' => $dateMock,
                'end'   => $dateMock
            ]
        );

        $this->expectReturn($this->parentAdapterMock, 'getData', [$this->adapterMock, $this->adapterMock]);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\SalePriceEffectiveDate',
            []
        );
    }

    public function testMap()
    {
        $this->expectReturn($this->parentAdapterMock, 'hasSpecialPrice', true);

        $this->model->addAdapter($this->parentAdapterMock);

        $params = ['column' => 'test'];
        $cell = $this->model->map($params);
        $this->assertEquals('2016-01-01T06:00:00-06:00/2016-01-01T06:00:00-06:00', $cell);
    }

    public function testMapNoSpecialPrice()
    {
        $this->expectReturn($this->parentAdapterMock, 'hasSpecialPrice', false);

        $this->model->addAdapter($this->parentAdapterMock);

        $params = ['column' => 'test'];
        $cell = $this->model->map($params);
        $this->assertEquals('', $cell);
    }
}
