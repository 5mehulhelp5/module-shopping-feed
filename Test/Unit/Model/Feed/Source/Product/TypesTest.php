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

namespace MageOS\ShoppingFeed\Test\Unit\Model\Feed\Source\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class TypesTest extends ModelFramework
{
    /** @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\Types */
    protected $sourceTypes;

    /**
     * @var \Magento\Catalog\Model\Product\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeMock;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->productTypeMock = $this->getModelMock('Magento\Catalog\Model\Product\Type', ['getTypes']);

        $this->productTypeMock->expects($this->once())
            ->method('getTypes')
            ->will($this->returnValue([
                'simple' => [
                    'name' => 'simple',
                    'label' => 'Simple Product',
                    'is_qty' => true,
                ],
                'virtual' => [
                    'name' => 'virtual',
                    'label' => 'Virtual Product',
                    'is_qty' => true,
                ]
            ]));

        $this->sourceTypes = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Feed\Source\Product\Types',
            [
                'productType' => $this->productTypeMock
            ]
        );
    }

    public function testGetProductTypes()
    {
        $this->assertEquals([
            'simple' => [
                'name' => 'simple',
                'label' => 'Simple Product',
                'is_qty' => true,
            ],
            'virtual' => [
                'name' => 'virtual',
                'label' => 'Virtual Product',
                'is_qty' => true,
            ]
        ], $this->sourceTypes->getProductTypes());
    }

    public function testToOptionArray()
    {
        $this->assertEquals([
            ['value' => 'simple', 'label' => 'Simple Product'],
            ['value' => 'virtual', 'label' => 'Virtual Product'],
        ], $this->sourceTypes->toOptionArray());
    }
}
