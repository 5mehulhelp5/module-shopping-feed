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


namespace MageOS\ShoppingFeed\Test\Unit\Model\Generator;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class FactoryTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class FactoryTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Factory
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
        $this->objectManagerMock = $this->getModelMock('Magento\Framework\ObjectManagerInterface', ['create', 'get', 'configure']);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Generator\Factory',
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testCreate()
    {
        $feedMock = $this->getModelMock('MageOS\ShoppingFeed\Model\Feed', []);
        $queue = 'fake';

        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->will($this->returnArgument(0));

        $this->assertEquals('MageOS\ShoppingFeed\Model\Generator', $this->model->create($feedMock, $queue));

        $feed = 10;
        $this->assertEquals('MageOS\ShoppingFeed\Model\Generator', $this->model->create($feed, $queue));
        $this->assertEquals('MageOS\ShoppingFeed\Model\Generator', $this->model->create($feed, $queue, 'testSKU'));
    }
}
