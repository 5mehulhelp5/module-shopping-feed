<?php
declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Controller\Adminhtml\Feed;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Builder;
use MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Test as Preview;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class TestTest extends TestCase
{
    #[DataProvider('invalidLookups')]
    public function testMalformedLookupsRenderAnErrorWithoutLoadingAProduct(array $input): void
    {
        [$controller, $factory, $messages, $page] = $this->controller($input);
        $factory->expects(self::never())->method('create');
        $messages->expects(self::once())->method('addErrorMessage');
        self::assertSame($page, $controller->execute());
    }

    public static function invalidLookups(): array
    {
        return [[['sku' => ['x'], 'type' => 'sku']], [['sku' => 'x', 'type' => ['sku']]],
            [['sku' => 'x', 'type' => 'invalid']], [['sku' => false, 'type' => 'sku']],
            [['sku' => '0', 'type' => 'id']], [['sku' => '-1', 'type' => 'id']],
            [['sku' => '1abc', 'type' => 'id']], [['sku' => '1.5', 'type' => 'id']],
            [['sku' => '999999999999999999999999', 'type' => 'id']]];
    }

    #[DataProvider('validLookups')]
    public function testPreservesSkuIdentityAndSeparatesTheLookupModeFromFeedType($sku, $type, $lookup): void
    {
        [$controller, $factory, $messages, $page, $builder, $registry] =
            $this->controller(['sku' => $sku, 'type' => $type]);
        $builder->expects(self::once())->method('build')->with(['id' => 17]);
        $messages->expects(self::never())->method('addErrorMessage');
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $factory->method('create')->willReturn($product);
        if ($type === 'id') {
            $product->expects(self::never())->method('getIdBySku');
            $product->expects(self::once())->method('load')->with($lookup)->willReturnSelf();
        } else {
            $product->expects(self::once())->method('getIdBySku')->with($lookup)->willReturn(42);
            $product->expects(self::once())->method('load')->with(42)->willReturnSelf();
        }
        $product->method('getId')->willReturn(42);
        $registry->expects(self::once())->method('register')->with('current_test_product', $product);
        self::assertSame($page, $controller->execute());
    }

    public static function validLookups(): array
    {
        return [['0', 'sku', '0'], ['00042', 'sku', '00042'], ['sku-1', null, 'sku-1'],
            ['0042', 'id', 42]];
    }

    public function testMalformedFeedIdentityProducesARecoverableMessage(): void
    {
        [$controller, $factory, $messages, , $builder, , $forward] = $this->controller([]);
        $builder->method('build')->willThrowException(new LocalizedException(__('Invalid id value.')));
        $factory->expects(self::never())->method('create');
        $messages->expects(self::once())->method('addErrorMessage')->with('Invalid id value.');
        $forward->expects(self::once())->method('forward')->with('index')->willReturnSelf();
        self::assertSame($forward, $controller->execute());
    }

    private function controller(array $input): array
    {
        $request = $this->getMockBuilder(Http::class)->disableOriginalConstructor()
            ->onlyMethods(['getParams', 'getParam'])->getMock();
        $params = $input + ['id' => 17];
        $request->method('getParams')->willReturn($params);
        $request->method('getParam')->willReturnCallback(static fn($key, $default = null) => $params[$key] ?? $default);
        $messages = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $context = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $context->method('getRequest')->willReturn($request);
        $context->method('getMessageManager')->willReturn($messages);
        $feed = $this->getMockBuilder(\MageOS\ShoppingFeed\Model\Feed::class)->disableOriginalConstructor()
            ->onlyMethods(['getId'])->getMock();
        $feed->method('getId')->willReturn(17);
        $feed->setData('name', 'Preview');
        $builder = $this->createMock(Builder::class);
        $builder->method('build')->willReturn($feed);
        $page = $this->createMock(\Magento\Framework\View\Result\Page::class);
        $config = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $config->method('getTitle')->willReturn($this->createMock(\Magento\Framework\View\Page\Title::class));
        $page->method('getConfig')->willReturn($config);
        $pages = $this->createMock(\Magento\Framework\View\Result\PageFactory::class);
        $pages->method('create')->willReturn($page);
        $forward = $this->createMock(\Magento\Backend\Model\View\Result\Forward::class);
        $forwards = $this->createMock(\Magento\Backend\Model\View\Result\ForwardFactory::class);
        $forwards->method('create')->willReturn($forward);
        $factory = $this->createMock(\Magento\Catalog\Model\ProductFactory::class);
        $registry = $this->createMock(\Magento\Framework\Registry::class);
        return [new Preview($context, $builder, $pages, $forwards, $factory, $registry),
            $factory, $messages, $page, $builder, $registry, $forward];
    }
}
