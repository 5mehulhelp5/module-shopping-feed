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

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Grouped\Associated;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class ConcatenateTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ConcatenateTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Grouped\Associated\Concatenate
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Grouped\Associated\Concatenate',
            []
        );
    }

    public function testMap()
    {
        $this->expectReturn($this->productMock, 'getId', 1);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->adapterMock, 'getData', 'store_code');
        $this->expectReturn(
            $this->feedMock, 'getColumnsMap', [
            ['column' => 'link', 'attribute' => 'directive_url'],
            ['column' => 'image_link', 'attribute' => 'image_link'],
            ['column' => 'test_attribute', 'attribute' => 'directive_concatenate']
            ]
        );
        $this->expectReturn($this->feedMock, 'getConfig', ['link']);
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectAdvencedReturn(
            $this->adapterMock,
            'getMapValue',
            $this->returnCallback(
                function ($arg) {
                    return $arg['attribute'] . "_value";
                }
            )
        );

        $this->model->addAdapter($this->adapterMock);

        $params = ['param' => '{{link}} - {{image_link}} - {{test_attribute}}'];
        $cell = $this->model->map($params);
        $this->assertEquals('directive_url_value - image_link_value - test_attribute_value', $cell);
    }

    public function testMapEmpty()
    {
        $this->expectReturn($this->feedMock, 'getColumnsMap', []);
        $this->expectReturn($this->feedMock, 'getConfig', ['link']);
        $this->expectReturn($this->productMock, 'getId', 1);
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->adapterMock, 'getData', 'store_code');
        $this->expectAdvencedReturn(
            $this->adapterMock,
            'getMapValue',
            $this->returnCallback(
                function ($arg) {
                    return '';
                }
            )
        );
        $this->expectReturn($this->adapterMock, 'mapEmptyValues', '');

        $this->model->addAdapter($this->adapterMock);

        $params = ['param' => '{{link}} - {{image_link}} - {{test_attribute}}'];
        $cell = $this->model->map($params);
        $this->assertEquals('', $cell);
    }
}
