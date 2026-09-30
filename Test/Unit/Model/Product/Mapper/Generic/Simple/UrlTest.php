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
 * Class UrlTest
 *
 * @package MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Simple
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class UrlTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Url
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->expectReturn($this->storeMock, 'getBaseUrl', 'http://base.url/');
        $this->expectReturn($this->productMock, 'getStore', $this->storeMock);
        $this->expectReturn($this->adapterMock, 'hasParentAdapter', true);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Url',
            []
        );
    }

    public function testMap()
    {
        $this->expectReturn($this->productMock, 'getProductUrl', 'some/product/url/');
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);

        $this->model->addAdapter($this->adapterMock);

        $params = ['column' => 'test', 'param' => '?passed=in'];
        $cell = $this->model->map($params);
        $this->assertEquals('http://base.urlsome/product/url/?passed=in', $cell);
    }

    public function testMapWithBaseUrl()
    {
        $this->productMock->expects($this->any())
            ->method('getProductUrl')
            ->will($this->returnValue('https://base.url/some/product/url/'));

        $this->adapterMock->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($this->productMock));

        $this->model->addAdapter($this->adapterMock);

        $params = ['column' => 'test'];
        $cell = $this->model->map($params);
        $this->assertEquals('http://base.url/some/product/url/', $cell);
    }
    public function testStorePortSurvivesUrlMapping(): void
    {
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->method('getBaseUrl')->willReturn('https://store.example:8443/');
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->method('getStore')->willReturn($store);
        $product->method('getProductUrl')->willReturn('https://store.example:8443/product.html');
        $adapter = $this->createMock(\MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple::class);
        $adapter->method('getProduct')->willReturn($product);
        $adapter->method('getFilter')->willReturn($this->createMock(\MageOS\ShoppingFeed\Model\Product\Filter::class));
        $this->model->addAdapter($adapter);
        $this->assertSame('https://store.example:8443/product.html?source=feed', $this->model->map(['column' => 'link', 'param' => 'source=feed']));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidUrls')]
    public function testMalformedUrlReturnsEmptyLink(string $base, string $url): void
    {
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->method('getBaseUrl')->willReturn($base);
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->method('getStore')->willReturn($store);
        $product->method('getProductUrl')->willReturn($url);
        $adapter = $this->createMock(\MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple::class);
        $adapter->method('getProduct')->willReturn($product);
        $adapter->method('getFilter')->willReturn($this->createMock(\MageOS\ShoppingFeed\Model\Product\Filter::class));
        $this->model->addAdapter($adapter);
        self::assertSame('', $this->model->map(['column'=>'link']));
    }

    public static function invalidUrls(): array
    {
        return [['http://shop.test:invalid/', '/item'], ['invalid', '/item'],
            ['https://shop.test/', 'http://shop.test:invalid/item'], ['https://shop.test/', '']];
    }

}
