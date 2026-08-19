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
 * Class ProductReviewCountTest
 *
 * @package MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Simple
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ProductReviewCountTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\ProductReviewCount
     */
    protected $model;

    protected $summaryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->expectReturn($this->productMock, 'getId', 1);

        $this->summaryMock = $this->getModelMock(
            '\Magento\Review\Model\Review\Summary',
            ['getReviewsCount', 'load']
        );
        $this->expectSelf($this->summaryMock, 'load');
        $this->expectReturn($this->summaryMock, 'getReviewsCount', 100);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\ProductReviewCount',
            [
                'summary' => $this->summaryMock
            ]
        );
    }

    public function testMap()
    {
        $this->model->addAdapter($this->adapterMock);

        $params = [];
        $cell = $this->model->map($params);
        $this->assertEquals(100, $cell);
    }

    public function testMapParent()
    {
        $this->expectReturn($this->adapterMock, 'hasParentAdapter', true);

        $this->model->addAdapter($this->adapterMock);

        $params = [];
        $cell = $this->model->map($params);
        $this->assertEquals(100, $cell);
    }
}
