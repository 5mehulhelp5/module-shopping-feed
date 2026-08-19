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

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Configurable\Associated;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class ProductTypeMagentoCategory
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ProductTypeMagentoCategoryTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\Associated\ProductTypeMagentoCategory
     */
    protected $model;


    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->expectSelf($this->cacheMock, 'setCache');
        $this->expectReturn($this->categoryCollectionFactoryMock, 'create', $this->categoryCollectionProvider);
        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\Associated\ProductTypeMagentoCategory',
            [
                'categoryFactory' => $this->categoryFactoryMock,
                'categoryCollectionFactory' => $this->categoryCollectionFactoryMock,
                'cache' => $this->cacheMock
            ]
        );
    }

    public function testMapEmpty()
    {
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->feedMock, 'getConfig', []);
        $this->expectReturn($this->categoryCollectionProvider, 'exportToArray', []);
        $this->expectReturn($this->productMock, 'getCategoryCollection', $this->categoryCollectionProvider);
        $this->expectSelf($this->categoryCollectionProvider, 'addFieldToFilter');

        $this->model->addAdapter($this->adapterMock);

        $expected = '';
        $cell = $this->model->map();
        $this->assertEquals($expected, $cell);
    }

    public function testMap()
    {
        $this->expectReturn($this->adapterMock, 'getMapValue', 'value');
        $this->model->addAdapter($this->adapterMock);

        $expected = 'value';
        $cell = $this->model->map();
        $this->assertEquals($expected, $cell);
    }
}
