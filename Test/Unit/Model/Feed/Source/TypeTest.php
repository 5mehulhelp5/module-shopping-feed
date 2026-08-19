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

// @codingStandardsIgnoreFile

namespace MageOS\ShoppingFeed\Test\Unit\Model\Feed\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class TypeTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Type
     */
    protected $type;

    /**
     * @var \MageOS\ShoppingFeed\Model\FeedTypes\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $feedTypesConfigMock;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->feedTypesConfigMock = $this->getModelMock(
            'MageOS\ShoppingFeed\Model\FeedTypes\Config',
            ['getAll']
        );

        $this->feedTypesConfigMock->expects($this->once())
            ->method('getAll')
            ->will($this->returnValue([
                0 => ['name' => 'foo', 'label' => 'Foo'],
                1 => ['name' => 'bar', 'label' => 'Bar'],
            ]));

        $this->type = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Feed\Source\Type',
            [
                'feedTypesConfig' => $this->feedTypesConfigMock,
            ]
        );
    }

    public function testGetOptionArray()
    {
        $this->assertEquals([
            'foo' => 'Foo',
            'bar' => 'Bar',
        ], $this->type->getOptionArray());
    }

    public function testToOptionArray()
    {
        $this->assertEquals([
            ['value' => 'foo', 'label' => 'Foo'],
            ['value' => 'bar', 'label' => 'Bar'],
        ], $this->type->toOptionArray());
    }
}
