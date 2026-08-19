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
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Class BundleTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class BundleTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Adapter\Type\Bundle
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
            'adapterFactory' => $this->adapterFactoryMock,
            'timezone' => $this->dateTimeMock,
            'stockState' => $this->stockStateMock,
            'date' => $this->dateMock,
            'cache' => $this->cacheMock,
            'filter' => $this->filterMock,
            'logger' => $this->loggerMock,
            'data' => []
        ];

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Adapter\Type\Bundle',
            $this->modelArguments
        );
    }

    public function testBeforeMap()
    {
        $this->expectSelf($this->productMock, ['getTypeInstance', 'getSelectionsCollection']);
        $this->expectReturn($this->productMock, 'getOptionsIds', ['id']);

        $productMock = $this->getModelMock(
            'Magento\Catalog\Model\Product',
            ['getId', 'getSku', 'isDisabled', 'getStoreId']
        );
        $this->expectAdvencedReturn($productMock, 'isDisabled', $this->onConsecutiveCalls(true, false));
        $this->expectReturn($productMock, 'getStoreId', 1);
        $this->expectReturn($this->productMock, 'addAttributeToSelect', [$productMock, $productMock]);

        $this->expectReturn($this->adapterFactoryMock, 'create', $this->adapterMock);

        $this->model->beforeMap();
        $this->assertEquals([$this->adapterMock], $this->model->getData('associated_product_adapters'));
    }

    /**
     * @dataProvider getPricesProvider
     *
     * @param $quantity
     * @param $algorithm
     * @param $expected
     */
    #[DataProvider('getPricesProvider')]
    public function testGetPrices($quantity, $algorithm, $expected)
    {
        $configMock = $this->getModelMock('\Magento\Framework\DataObject', ['getAlgorithm']);
        $this->expectReturn($configMock, 'getAlgorithm', $algorithm);
        $this->expectReturn($this->taxHelperMock, 'getConfig', $configMock);
        $this->expectReturn($this->helperMock, 'getQuantityIcrements', $quantity);
        $this->expectReturn($this->weeHelperMock, 'getAmountExclTax', 0);
        $this->expectReturn($this->weeHelperMock, 'isTaxable', true);
        $this->expectReturn($this->weeHelperMock, 'getProductWeeeAttributesForRenderer', []);
        $this->expectAdvencedReturn(
            $this->catalogHelperMock,
            'getTaxPrice',
            $this->returnCallback(
                function ($product, $price, $tax = false) {
                    return $tax ? $price * 1.2 : $price * 1.0;
                }
            )
        );

        $this->expectReturn($this->productMock, 'getSpecialPrice', 200);
        $this->expectSelf($this->storeMock, 'getBaseCurrency');
        $regularAmount = new DataObject(['value' => 200]);
        $finalAmount = new DataObject(['value' => 100]);
        $regularPrice = new DataObject(['minimal_price' => $regularAmount]);
        $finalPrice = new DataObject(['minimal_price' => $finalAmount]);
        $priceInfo = $this->getModelMock('Magento\Framework\DataObject', ['getPrice']);
        $this->expectAdvencedReturn(
            $priceInfo,
            'getPrice',
            $this->returnCallback(
                static function ($code) use ($regularPrice, $finalPrice) {
                    return $code === 'regular_price' ? $regularPrice : $finalPrice;
                }
            )
        );
        $this->expectReturn($this->productMock, 'getPriceInfo', $priceInfo);

        $this->expectAdvencedReturn($this->storeMock, 'convert', $this->returnArgument(0));
        $this->expectReturn($this->catalogProductPriceMock, 'calculatePrice', 250);

        // Testing the method
        $prices = $this->model->getPrices();
        $this->assertEquals($expected, $prices);

        // Testing the cache
        $prices = $this->model->getPrices();
        $this->assertEquals($expected, $prices);
    }

    public static function getPricesProvider()
    {
        return [
            [1, '', [
                'p_excl_tax' => 200.0,
                'p_incl_tax' => 240.0,
                'sp_excl_tax' => 100.0,
                'sp_incl_tax' => 120.0
            ]],
            [10, '', [
                'p_excl_tax' => 2000.0,
                'p_incl_tax' => 2400.0,
                'sp_excl_tax' => 1000.0,
                'sp_incl_tax' => 1200.0
            ]],
            [10, 'UNIT_BASE_CALCULATION', [
                'p_excl_tax' => 2000.0,
                'p_incl_tax' => 2400.0,
                'sp_excl_tax' => 1000.0,
                'sp_incl_tax' => 1200.0
            ]],
        ];
    }

    public function testGetAssociatedMapColumnsFromParent()
    {
        $this->expectReturn($this->feedMock, 'getConfig', []);
        $this->assertEquals([], $this->model->getAssociatedMapInheritance());
    }

    public function testGetAssociatedProductsMode()
    {
        $this->assertEquals([], $this->model->getAssociatedMapInheritance());
    }

    public function testGetChildrenCount()
    {
        $this->model->setData('associated_product_adapters', []);
        $this->assertEquals(1, $this->model->getChildrenCount());
    }
}
