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


namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Category;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class CollectionProviderTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CollectionProviderTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Category\CollectionProvider
     */
    protected $model;


    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        parent::setUp();

        $storeManager = $this->getModelMock('Magento\Store\Model\StoreManager', ['getStore']);
        $this->expectReturn($storeManager, 'getStore', $this->storeMock);
        $this->expectReturn($this->feedMock, 'getConfig', true);
        $this->expectReturn($this->feedMock, 'getStoreId', 1);


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
            ['addAttributeToSelect', 'setStoreId', 'addFieldToFilter', 'addLevelFilter', 'addAttributeToSort']
        );
        $this->expectReturn($this->categoryCollectionFactoryMock, 'create', $collection);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Category\CollectionProvider',
            [
                'categoryCollectionFactory' => $this->categoryCollectionFactoryMock,
                'storeManager' => $storeManager
            ]
        );
    }

    public function testGetCategories()
    {
        $categories = $this->model->getCategories($this->feedMock);
        $expected = [[
                'name' => 'A',
                'id' => 1,
                'path' => '1/1',
                'parent_id' => 0,
                'level' => 1,
                'store_active' => null,
                'children' => 1,
            ],[
                'name' => 'B',
                'id' => 2,
                'path' => '1/1/2',
                'parent_id' => 1,
                'level' => 2,
                'store_active' => null,
                'children' => 0
            ]];
        $this->assertEquals($expected, $categories);
    }
}
