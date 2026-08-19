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


namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Adapter\Type;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class ConfigurableTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ConfigurableTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Adapter\Type\Configurable
     */
    protected $model;

    /**
     * @var array
     */
    protected $modelArguments = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->expectReturn($this->productMock, 'getId', 1);
        $this->expectReturn($this->productMock, 'getSku', 'S-K-U');
        $this->expectReturn($this->feedMock, 'getId', 1);
        $this->expectReturn($this->feedMock, 'getStore', $this->storeMock);
        $this->expectReturn($this->feedMock, 'getType', 'google_shopping');

        $this->expectReturn($this->stockStateMock, 'getStockQty', 5);
        $this->expectSelf($this->storeMock, 'getCurrentCurrency');

        $filesystemMock = $this->getModelMock('Magento\Framework\Filesystem');
        $directoryMock = $this->getModelMock('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->expectReturn($filesystemMock, 'getDirectoryRead', $directoryMock);

        $this->modelArguments = [
            'filesystem' => $filesystemMock,
            'feed' => $this->feedMock,
            'product' => $this->productMock,
            'feedTypesConfig' => $this->feedTypesConfigMock,
            'mapperFactory' => $this->mapperFactoryMock,
            'helper' => $this->helperMock,
            'weeeData' => $this->weeHelperMock,
            'taxData' => $this->taxHelperMock,
            'catalogHelper' => $this->catalogHelperMock,
            'productTypePrice' => $this->catalogProductPriceMock,
            'optionFactory' => $this->optionFactoryMock,
            'processFactory' => $this->processFactoryMock,
            'adapterFactory' => $this->adapterFactoryMock,
            'timezone' => $this->dateTimeMock,
            'stockState' => $this->stockStateMock,
            'date' => $this->dateMock,
            'cache' => $this->cacheMock,
            'filter' => $this->filterMock,
            'logger' => $this->loggerMock,
            'generator' => $this->generatorMock,
            'data' => []
        ];

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Adapter\Type\Configurable',
            $this->modelArguments
        );
    }

    public function testMap()
    {
        $this->expectSelf($this->productMock, ['getTypeInstance', 'getUsedProductCollection']);
        $this->expectReturn($this->storeMock, 'getStoreId', 1);

        $this->expectAdvencedReturn(
            $this->feedMock,
            'getConfig',
            $this->returnCallback(function ($key, $default = '') {
                switch ($key) {
                    case 'configurable_associated_products_mode':
                        return 2;
                    case 'configurable_map_inherit':
                        return [['column' => 'title', 'from' => 0]];
                    case 'filters_skip_column_empty':
                        return ['emptyName'];
                    default:
                        return $default;
                }
            })
        );

        $productMock = $this->getModelMock(
            'Magento\Catalog\Model\Product',
            ['getId', 'getSku', 'isDisabled', 'getStoreId']
        );
        $this->expectAdvencedReturn($productMock, 'isDisabled', $this->onConsecutiveCalls(true, false));
        $this->expectReturn($productMock, 'getStoreId', 1);
        $this->expectReturn($this->productMock, 'addAttributeToSelect', [$productMock, $productMock]);

        $this->expectReturn($this->adapterMock, 'getMapValue', 'columnValue');
        $this->expectReturn($this->adapterFactoryMock, 'create', $this->adapterMock);

        $this->expectAdvencedReturn(
            $this->cacheMock,
            'getCache',
            $this->returnCallback(function ($path, $default) {
                if ($path == ['row', 'map', 'product', 1, 'column', 'columnName']
                    || $path == ['row', 'map', 'product', 1, 'column', 'parentName']) {
                    return 'columnValue';
                } else {
                    return '';
                }
            })
        );

        $this->expectReturn($this->feedTypesConfigMock, 'isAllowedDirective', false);
        $this->expectReturn($this->feedMock, 'getColumnsMap', [
                ['column' => 'columnName', 'attribute' => 'test'],
                ['column' => 'columnName', 'attribute' => 'test'],
                ['column' => 'columnName', 'attribute' => 'test'],
                ['column' => 'parentName', 'attribute' => 'test'],
                ['column' => 'emptyName', 'attribute' => 'test']
            ]);

        $this->expectReturn($this->adapterMock, 'getConfig', $this->configMock);
        $this->adapterMock->setFeed($this->feedMock);
        $this->model->setData('associated_product_adapters', [$this->adapterMock, $this->adapterMock]);
        $this->model->setData('generator', $this->generatorMock);

        $expected = [
            1 => [
                'columnName' => ['columnValue', 'columnValue', 'columnValue'],
                'parentName' => 'columnValue',
                'emptyName' => 'columnValue'
            ],
            2 => [
                'columnName' => ['columnValue', 'columnValue', 'columnValue'],
                'parentName' => 'columnValue',
                'emptyName' => 'columnValue'
            ]

        ];

        $result = $this->model->map();
        $this->assertEquals($expected, $result);
    }


    public function testGetConfigurableUrlOptions()
    {
        $this->expectSelf($this->productMock, 'getTypeInstance');

        $attribute = $this->getModelMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute',
            ['getProductAttribute', 'getAttributeCode', 'getAttributeId']
        );
        $this->expectSelf($attribute, 'getProductAttribute');
        $this->expectReturn($attribute, 'getAttributeCode', 'attribute_code');
        $this->expectReturn($attribute, 'getAttributeId', 'attribute_id');
        $this->expectReturn($this->productMock, 'getConfigurableAttributes', [$attribute, $attribute]);
        $this->expectAdvencedReturn($this->productMock, 'hasData', $this->onConsecutiveCalls(true, false));
        $this->expectReturn($this->productMock, 'getData', 'attribute_value');
        $this->expectReturn($this->feedMock, 'getConfig', true);

        $expected = ['attribute_id' => 'attribute_value'];
        $this->assertEquals($expected, $this->model->getUrlOptions($this->productMock));
    }

    public function testHasSpecialPrice()
    {
        $this->expectAdvencedReturn($this->adapterMock, 'hasSpecialPrice', $this->onConsecutiveCalls(false, true));

        $this->model->setData('associated_product_adapters', [$this->adapterMock, $this->adapterMock]);

        $this->assertEquals(true, $this->model->hasSpecialPrice());
    }

    public function testGetAssociatedMapColumnsFromParent()
    {
        $this->expectReturn($this->feedMock, 'getConfig', []);
        $this->assertEquals([], $this->model->getAssociatedMapInheritance());
    }

    public function testGetAssociatedProductsMode()
    {
        $this->expectReturn($this->feedMock, 'getConfig', 1);
        $this->assertEquals(1, $this->model->getAssociatedMapInheritance());
    }

    public function testGetChildrenCount()
    {
        $this->model->setData('associated_product_adapters', []);
        $this->assertEquals(1, $this->model->getChildrenCount());
    }
}
