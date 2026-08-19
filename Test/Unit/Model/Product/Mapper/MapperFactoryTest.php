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
 * Class MapperFactoryTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class MapperFactoryTest extends CompatibilityTestCase
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\MapperFactory
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    public function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->objectManagerMock = $this->createCompatibleMock(
            'Magento\Framework\ObjectManagerInterface',
            ['create', 'get', 'configure']
        );

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\MapperFactory',
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testCreate()
    {
        /**
 * @var \MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple|\PHPUnit_Framework_MockObject_MockObject $adapter
*/
        $adapter = $this->createCompatibleMock(
            'MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple',
            ['getProduct', 'getTypeId']
        );
        $adapter->expects($this->any())
            ->method('getProduct')
            ->will($this->returnSelf());
        $adapter->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue('simple'));

        $directive = [
            'mappers' => [
                'default' => [],
                'simple'  => [
                    'type' => 'Fake\Class\Name',
                    'configuration' => [
                        'key' => 'value'
                    ]
                ]
            ]
        ];

        $instance = $this->getMockBuilder('MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($instance));

        $this->assertInstanceOf(
            'MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract',
            $this->model->create($directive, $adapter)
        );
    }
}
