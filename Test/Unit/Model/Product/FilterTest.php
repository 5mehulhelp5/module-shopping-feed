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


namespace MageOS\ShoppingFeed\Test\Unit\Model\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\CompatibilityTestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class FilterTest extends CompatibilityTestCase
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Filter
     */
    protected $model;

    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $feedMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->cacheMock = $this->createCompatibleMock(
            'MageOS\ShoppingFeed\Model\Generator\Cache',
            ['getCache']
        );

        $this->feedMock = $this->createCompatibleMock(
            'MageOS\ShoppingFeed\Model\Feed',
            ['getId', 'getConfig']
        );

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Filter',
            [
                'cache' => $this->cacheMock
            ]
        );
    }

    public function testFindAndReplace()
    {
        $params = 'columnName';
        $string = 'String: FIND STRING, FIND ALL';

        $this->cacheMock->expects($this->any())
            ->method('getCache')
            ->will(
                $this->onConsecutiveCalls(
                    true, [
                    'columnName' => ['find' => 'FIND STRING', 'replace' => 'REPLACE STRING'],
                    '-all-' => ['find' => 'FIND ALL', 'replace' => 'REPLACE ALL']
                    ]
                )
            );

        $this->feedMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->feedMock->expects($this->any())
            ->method('getConfig')
            ->will(
                $this->returnValue(
                    [
                    ['columns' => '', 'find' => 'find string', 'replace' => 'replace string'],
                    ['columns' => 'column1', 'find' => 'find string', 'replace' => 'replace string'],
                    ]
                )
            );
        $this->model->setFeed($this->feedMock);

        $expected = 'String: REPLACE STRING, REPLACE ALL';
        $this->model->findAndReplace($string, $params);
        $this->assertEquals($expected, $string);
    }


    public function testCleanField()
    {
        $params = ['column' => 'columnName'];
        $field = ' A"\'B' . "\nC <span>content</span><br /> > path&nbsp;SPACE\t";

        $this->feedMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $this->model->setFeed($this->feedMock);

        $this->cacheMock->expects($this->any())
            ->method('getCache')
            ->will($this->returnValue([]));

        $this->assertEquals('A"\'B C content > path SPACE', $this->model->cleanField($field, $params));
    }
}
