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


namespace MageOS\ShoppingFeed\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\CompatibilityTestCase;

/**
 * Class LoggerTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class LoggerTest extends CompatibilityTestCase
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Logger
     */
    protected $model;

    /**
     * @var \MageOS\ShoppingFeed\Model\Logger\HandlerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $handlerFactoryMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    public function setUp(): void
    {
        $this->handlerFactoryMock = $this->createCompatibleMock(
            'MageOS\ShoppingFeed\Model\Logger\HandlerFactory',
            ['create']
        );

        $this->handlerFactoryMock->expects($this->any())
            ->method('create')
            ->will(
                $this->returnCallback(
                    function ($path, $level) {
                        return sprintf('%s_%s', $path, $level);
                    }
                )
            );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Logger',
            [
                'handlerFactory' => $this->handlerFactoryMock
            ]
        );
    }

    /**
     * Test adding multiple handlers
     */
    public function testAddHandler()
    {
        $this->model->addHandler('/path1');
        $this->model->addHandler('/path2');

        $expected = [
            sprintf('%s_%s', '/path1', \Monolog\Logger::INFO),
            sprintf('%s_%s', '/path2', \Monolog\Logger::INFO),
        ];
        $this->assertEquals($expected, $this->model->getHandlers());
    }

    /**
     * Test that setting handler will set to default + given only, the rest of them are removed
     */
    public function testSetHandler()
    {
        $this->model->addHandler('/path1');
        $this->model->addHandler('/path2');

        $this->model->setHandler('/only_path');
        $expected = [
            sprintf('%s_%s', '/only_path', \Monolog\Logger::INFO)
        ];
        $this->assertEquals($expected, $this->model->getHandlers());
    }

    public function testResetHandler()
    {
        $this->model->addHandler('/path3');
        $this->model->resetHandler();
        $this->model->addHandler('/path4');

        $expected = [
            sprintf('%s_%s', '/path4', \Monolog\Logger::INFO)
        ];
        $this->assertEquals($expected, $this->model->getHandlers());
    }
}
