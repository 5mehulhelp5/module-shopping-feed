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


namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\CompatibilityTestCase;

/**
 * Class MapperAbstractTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class MapperAbstractTest extends CompatibilityTestCase
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    public function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Id',
            []
        );
    }

    public function testAdapter()
    {
        $adapter = $this->createCompatibleMock(
            'MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple',
            ['getTitle']
        );
        $adapter->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue('adapter'));

        $adapter2 = $this->createCompatibleMock(
            'MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple',
            ['getTitle']
        );
        $adapter2->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue('adapter2'));

        $this->model->addAdapter($adapter);
        $this->model->addAdapter($adapter2);

        $this->assertEquals('adapter2', $this->model->getAdapter()->getTitle());

        $this->model->popAdapter();

        $this->assertEquals('adapter', $this->model->getAdapter()->getTitle());
    }

    public function testConfiguration()
    {
        $this->model->setConfiguration('path', 'value');

        $this->assertEquals(true, $this->model->hasConfiguration('path'));
        $this->assertEquals(false, $this->model->hasConfiguration('non-existing'));

        $this->assertEquals('value', $this->model->getConfiguration('path'));
        $this->assertEquals(false, $this->model->getConfiguration('non-existing'));

        $this->model->resetConfiguration();
        $this->assertEquals(false, $this->model->getConfiguration('path'));
    }
}
