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
 * Class VariantAttributesTest
 *
 * @package MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Configurable
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class VariantAttributesTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\VariantAttributes
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->expectReturn($this->productMock, 'hasData', true);
        $this->expectAdvencedReturn($this->adapterMock, 'getMapAttribute', $this->returnArgument(0));
        $this->expectReturn($this->feedMock, 'getConfig', '|');
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);

        $this->parentAdapterMock = clone $this->adapterMock;

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\VariantAttributes',
            []
        );
        $this->model->addAdapter($this->parentAdapterMock);
    }

    public function testMap()
    {
        $this->expectAdvencedReturn(
            $this->parentAdapterMock,
            'getAttributeValue',
            $this->returnCallback(
                function ($product, $arg) {
                    return $arg . '_value';
                }
            )
        );

        $params = ['param' => [
            'attribute_code1',
            'attribute_code2',
            'attribute_code3'
        ]];
        $cell = $this->model->map($params);
        $this->assertEquals('attribute_code1_value|attribute_code2_value|attribute_code3_value', $cell);
    }

    public function testMapEmpty()
    {
        $params = ['param' => []];
        $cell = $this->model->map($params);
        $this->assertEquals('', $cell);
    }

    public function testMapEmptyOnParent()
    {
        $this->expectReturn($this->parentAdapterMock, 'getAttributeValue', '');
        $this->expectReturn($this->parentAdapterMock, 'getMapValue', 'parent_value');
        $this->expectReturn($this->parentAdapterMock, 'getData', [$this->adapterMock]);

        $this->expectAdvencedReturn(
            $this->adapterMock,
            'getAttributeValue',
            $this->returnCallback(
                function ($product, $arg) {
                    return $arg . '_assoc_value';
                }
            )
        );

        $params = ['param' => [
            'attribute_code1',
            'attribute_code2',
            'attribute_code3'
        ]];
        $expected = 'attribute_code1_assoc_value|attribute_code2_assoc_value|attribute_code3_assoc_value';
        $cell = $this->model->map($params);
        $this->assertEquals($expected, $cell);
    }
}
