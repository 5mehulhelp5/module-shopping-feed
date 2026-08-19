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
 * Class FeedTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CategoryImageLinkTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\CategoryImageLink
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->expectReturn($this->productMock, 'getCategoryIds', [1,2]);

        $category = $this->getModelMock(
            '\Magento\Catalog\Model\Category',
            ['getImageUrl', 'hasChildren']
        );
        $this->expectReturn($category, 'getImageUrl', 'url/image_url.png');
        $this->expectReturn($category, 'hasChildren', false);

        $this->expectSelf($this->categoryFactoryMock, 'create');
        $this->expectReturn($this->categoryFactoryMock, 'load', $category);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\CategoryImageLink',
            [
                'cache' => $this->cacheMock,
                'categoryFactory' => $this->categoryFactoryMock
            ]
        );
    }

    public function testMap()
    {
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectAdvencedReturn($this->cacheMock, 'getCache', $this->onConsecutiveCalls(true, 'url/image_url.png'));

        $this->model->addAdapter($this->adapterMock);

        $params = ['column' => 'fake'];
        $cell = $this->model->map($params);
        $this->assertEquals('url/image_url.png', $cell);
    }
}
