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
 * Class ProductTypeMagentoCategory
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ProductTypeMagentoCategoryTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\ProductTypeMagentoCategory
     */
    protected $model;


    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->expectSelf($this->cacheMock, 'setCache');

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $categoryMock = $this->getModelMock(
            'Magento\Catalog\Model\Category',
            ['getName', 'getId', 'getPath', 'getParentId', 'getLevel', 'getIsActive', ]
        );
        $this->expectAdvencedReturn($categoryMock, 'getId', $this->onConsecutiveCalls(1, 1, 2, 2));
        $this->expectAdvencedReturn($categoryMock, 'getParentId', $this->onConsecutiveCalls(0, 0, 1, 1, 1));
        $this->expectAdvencedReturn($categoryMock, 'getLevel', $this->onConsecutiveCalls(1, 2));
        $this->expectAdvencedReturn($categoryMock, 'getName', $this->onConsecutiveCalls('A', 'B'));
        $this->expectAdvencedReturn($categoryMock, 'getPath', $this->onConsecutiveCalls('1/1', '1/1/2'));

        $collection = $this->objectManagerHelper->getCollectionMock(
            'Magento\Catalog\Model\ResourceModel\Category\Collection',
            [$categoryMock, $categoryMock]
        );
        $this->expectSelf(
            $collection,
            ['addAttributeToSelect', 'setStoreId', 'addPathFilter', 'addLevelFilter',
                'addAttributeToSort', 'addIsActiveFilter', 'addFieldToFilter']
        );
        $this->expectReturn($collection, 'exportToArray', [
            4 => ['path' => '1/2/4'],
            5 => ['path' => '1/2/5'],
            6 => ['path' => '1/3/6'],
            7 => ['path' => '1/3/4']
        ]);
        $this->expectReturn($this->categoryCollectionFactoryMock, 'create', $collection);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\ProductTypeMagentoCategory',
            [
                'logger' => $this->loggerMock,
                'categoryFactory' => $this->categoryFactoryMock,
                'cache' => $this->cacheMock,
                'categoryCollectionFactory' => $this->categoryCollectionFactoryMock
            ]
        );
    }

    public function testMapEmpty()
    {
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->feedMock, 'getConfig', []);

        $this->model->addAdapter($this->adapterMock);

        $expected = '';
        $cell = $this->model->map();
        $this->assertEquals($expected, $cell);
    }

    public function testMap()
    {
        $this->expectReturn($this->productMock, 'getCategoryIds', [1,5,6]);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->adapterMock, 'getStore', $this->storeMock);
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn($this->productMock, 'getCategoryCollection', $this->categoryCollectionProvider);
        $this->expectReturn($this->feedMock, 'getConfig', [['p' => 5, 'id' => 2, 'ty' => 'value'], ['p' => 10, 'id' => 1, 'ty' => 'value 2']]);


        $this->expectAdvencedReturn(
            $this->cacheMock,
            'getCache',
            $this->returnCallback(function ($key, $default = []) {
                switch ($key) {
                    case ['row', 'map', 'category', 1, 'path']:
                        return [];
                    case ['row', 'map', 'category', 5, 'path']:
                        return ['root', 'category_tst'];
                    default:
                        return $key;
                }
                return $default;
            })
        );

        $this->model->addAdapter($this->adapterMock);

        $expected = 'row > map > category > 6 > path, root > category_tst';
        $cell = $this->model->map();
        $this->assertEquals($expected, $cell);
    }
}
