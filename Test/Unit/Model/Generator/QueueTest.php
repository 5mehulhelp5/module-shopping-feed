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

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class QueueTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class QueueTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Queue
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    protected $generatorMock;

    public function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->generatorMock = $this->getModelMock('MageOS\ShoppingFeed\Model\Generator');

        $generatorFactoryMock = $this->getModelMock('MageOS\ShoppingFeed\Model\Generator\Factory', ['create']);
        $generatorFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->generatorMock));

        $feedFactoryMock = $this->getModelMock('MageOS\ShoppingFeed\Model\FeedFactory', ['create', 'load']);
        $feedFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnSelf());

        $batchFactory = $this->getModelMock('MageOS\ShoppingFeed\Model\Generator\BatchFactory', ['create']);
        $batchFactory->expects($this->any())
            ->method('create')
            ->will($this->returnSelf());

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Generator\Queue',
            [
                'generatorFactory'  => $generatorFactoryMock,
                'feedFactory'       => $feedFactoryMock,
                'batchFactory'      => $batchFactory
            ]
        );
    }

    public function testGetGenerator()
    {
        $this->assertEquals($this->generatorMock, $this->model->getGenerator());
    }

    public function testSetGetBatch()
    {
        $batch = $this->getModelMock('MageOS\ShoppingFeed\Model\Generator\Batch');

        $this->model->setBatch($batch);
        $this->assertEquals($batch, $this->model->getBatch());
    }
}
