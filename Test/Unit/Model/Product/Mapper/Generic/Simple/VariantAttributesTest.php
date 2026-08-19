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
 * Class VariantAttributesTest
 *
 * @package MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Simple
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class VariantAttributesTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\VariantAttributes
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

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\VariantAttributes',
            []
        );
        $this->model->addAdapter($this->adapterMock);
    }

    public function testMap()
    {
        $this->expectAdvencedReturn(
            $this->adapterMock,
            'getAttributeValue',
            $this->returnCallback(
                function ($product, $arg) {
                    return $arg['attribute'] . '_value';
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
        $this->expectReturn($this->adapterMock, 'getAttributeValue', '');
        $this->expectReturn($this->adapterMock, 'hasParentAdapter', true);
        $this->expectReturn($this->adapterMock, 'getMapValue', 'parent_value');

        $params = ['param' => [[
            'attribute_code1',
            'attribute_code2',
            'attribute_code3'
        ]]];
        $cell = $this->model->map($params);
        $this->assertEquals('parent_value', $cell);
    }
}
