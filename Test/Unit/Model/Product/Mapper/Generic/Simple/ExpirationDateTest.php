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
class ExpirationDateTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\ExpirationDate
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
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\ExpirationDate',
            []
        );
    }

    public function testMap()
    {
        $this->model->addAdapter($this->adapterMock);

        $dateMock = new \DateTime('@1451649600');

        $this->expectReturn($this->dateTimeMock, 'date', $dateMock);
        $this->expectReturn($this->adapterMock, 'getTimezone', $this->dateTimeMock);

        $params = ['param' => 0];
        $cell = $this->model->map($params);
        $this->assertEquals('2016-01-01T12:00+0000', $cell);

        $params = ['param' => 10];
        $cell = $this->model->map($params);
        $this->assertEquals('2016-01-10T12:00+0000', $cell);
    }
}
