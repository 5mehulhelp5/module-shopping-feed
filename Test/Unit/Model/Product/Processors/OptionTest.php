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


namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Processors;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class OptionTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Processors\Option
     */
    protected $model;

    protected $valueMockArray = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->expectReturn($this->productMock, 'getId', 1);
        $this->expectReturn($this->productMock, 'getSku', 'product_sku');

        $this->expectReturn($this->optionMock, 'getTitle', 'Option name');
        $this->expectReturn($this->optionMock, 'getId', 1);
        $this->expectReturn($this->optionMock, 'getGroupByType', 'checkbox');

        $valueMock1 = $this->prepareValueMock(1);
        $valueMock2 = $this->prepareValueMock(2);
        $this->valueMockArray = [$valueMock1, $valueMock2];
        $this->expectReturn($this->optionMock, 'getValues', $this->valueMockArray);
        $this->expectReturn($this->productMock, 'getOptions', [$this->optionMock]);

        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn(
            $this->feedMock, 'getColumnsMap', [[
                'column' => 'Column',
                'attribute' => 'directive_product_option',
                'param' => 'Option name'
            ],[
                'column' => 'id',
                'attribute' => 'directive_id'
            ],[
                'column' => 'sku',
                'attribute' => 'sku'
            ],[
                'column' => 'item_group_id',
                'attribute' => 'directive_item_group_id'
            ],[
                'column' => 'item_group_title',
                'attribute' => 'directive_item_group_title'
            ],[
                'column' => 'variant_option',
                'attribute' => 'directive_variant_option'
            ]]
        );
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);

        $pricingHelperMock = $this->getModelMock('Magento\Framework\Pricing\Helper\Data', ['currency']);
        $this->expectAdvencedReturn($pricingHelperMock, 'currency', $this->returnArgument(0));

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Processors\Option',
            [
                'pricingHelper' => $pricingHelperMock
            ]
        );
        $this->model->setAdapter($this->adapterMock);
    }

    public function testProcess()
    {
        $rows = [
            [
                'id' => '1',
                'sku' => 'original_sku',
                'Column' => '',
                'title' => 'Product title',
                'price' => 2000,
                'sale_price' => 1000,
                'item_group_id' => 'item_group_id',
                'item_group_title' => '',
                'variant_option' => '',
            ]
        ];

        $expected = [[
            'id' => '1-option0',
            'sku' => 'sku-1',
            'Column' => 'title 1',
            'title' => 'Product title',
            'price' => 2100,
            'sale_price' => 1100,
            'item_group_id' => 'product_sku',
            'item_group_title' => 'Product title',
            'variant_option' => 'Column:title 1',
        ],[
            'id' => '1-option1',
            'sku' => 'sku-2',
            'Column' => 'title 2',
            'title' => 'Product title',
            'price' => 2200,
            'sale_price' => 1200,
            'item_group_id' => 'product_sku',
            'item_group_title' => 'Product title',
            'variant_option' => 'Column:title 2',
        ]];
        $output = $this->model->execute($rows);
        $this->assertEquals($expected, $output);
    }

    public function testGetOptions()
    {
        $options = $this->model->getOptions();
        $this->assertEquals(['Column' => $this->valueMockArray], $options);
    }

    protected function prepareValueMock($count)
    {
        $valueMock = $this->getModelMock(
            '\Magento\Catalog\Model\Product\Option\Value',
            ['getTitle', 'getPrice', 'getId', 'getSku', 'getProduct']
        );

        $this->expectReturn($valueMock, 'getTitle', sprintf('title %s', $count));
        $this->expectReturn($valueMock, 'getPrice', (int)sprintf('%s00', $count));
        $this->expectReturn($valueMock, 'getId', $count);
        $this->expectReturn($valueMock, 'getSku', sprintf('sku-%s', $count));
        $this->expectReturn($valueMock, 'getProduct', $this->productMock);

        return $valueMock;
    }
}
