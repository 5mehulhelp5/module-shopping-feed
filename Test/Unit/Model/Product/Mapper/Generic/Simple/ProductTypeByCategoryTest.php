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
 * Class ProductTypeByCategory
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ProductTypeByCategoryTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\ProductTypeByCategory
     */
    protected $model;


    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\ProductTypeByCategory',
            [
                'categoryProvider' => $this->categoryCollectionProvider,
                'cache' => $this->cacheMock
            ]
        );
    }

    public function testGetSortedTaxonomyMap()
    {
        $this->expectReturn($this->feedMock, 'getId', 1);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn($this->cacheMock, 'getCache', false);
        $this->expectAdvencedReturn(
            $this->feedMock,
            'getConfig',
            $this->returnCallback(function ($arg) {
                switch ($arg) {
                    case 'categories_provider_taxonomy_by_category':
                        return [
                            1 => ['p' => 10, 'id' => 1],
                            2 => ['p' => 5, 'id' => 2]
                        ];
                    case 'categories_sort_mode':
                        return 1;
                    default:
                        return '';
                }
            })
        );
        $this->expectReturn($this->categoryCollectionProvider, 'getCategories', [
            1 => ['level' => 1],
            2 => ['level' => 2]
        ]);

        $this->model->addAdapter($this->adapterMock);

        $expected = [['p' => 5, 'id' => 2], ['p' => 10, 'id' => 1]];
        $cell = $this->model->getSortedTaxonomyMap();
        $this->assertEquals($expected, $cell);
    }

    public function testGetSortedTaxonomyMapOrderByLevel()
    {
        $this->expectReturn($this->feedMock, 'getId', 1);
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn($this->cacheMock, 'getCache', false);
        $this->expectAdvencedReturn(
            $this->feedMock,
            'getConfig',
            $this->returnCallback(function ($arg) {
                switch ($arg) {
                    case 'categories_provider_taxonomy_by_category':
                        return [
                            1 => ['p' => 10, 'id' => 1],
                            2 => ['p' => 5, 'id' => 2]
                        ];
                    case 'categories_sort_mode':
                        return 0;
                    default:
                        return '';
                }
            })
        );
        $this->expectReturn($this->categoryCollectionProvider, 'getCategories', [
            1 => ['level' => 1],
            2 => ['level' => 2]
        ]);

        $this->model->addAdapter($this->adapterMock);

        $expected = [['p' => 5, 'id' => 2], ['p' => 10, 'id' => 1]];
        $cell = $this->model->getSortedTaxonomyMap();
        $this->assertEquals($expected, $cell);
    }

    public function testMap()
    {
        $this->expectReturn($this->feedMock, 'getId', 1);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn(
            $this->cacheMock,
            'getCache',
            [['p' => 5, 'id' => 2, 'ty' => 'value'], ['p' => 10, 'id' => 1, 'ty' => 'value 2']]
        );
        $this->expectReturn($this->productMock, 'getCategoryIds', [1,2]);
        $this->model->addAdapter($this->adapterMock);

        $params = ['column' => 'columnName'];
        $cell = $this->model->map($params);

        $this->assertEquals('value', $cell);
    }
}
