<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use MageOS\ShoppingFeed\Ui\Component\Listing\Column\FeedActions;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class FeedActionsTest extends TestCase
{
    public function testGenerateActionUsesMagentoPostActionTransport(): void
    {
        $urlBuilder = $this->createMock(UrlInterface::class);
        $urlBuilder->method('getUrl')->willReturnCallback(
            static fn (string $route, array $params = []): string => $route . '?id=' . $params['id']
        );

        $column = new FeedActions(
            $this->createMock(ContextInterface::class),
            $this->createMock(UiComponentFactory::class),
            $urlBuilder,
            [],
            ['name' => 'actions']
        );

        $result = $column->prepareDataSource(['data' => ['items' => [['id' => 7]]]]);
        $actions = $result['data']['items'][0]['actions'];

        $this->assertTrue($actions['generate']['post']);
        $this->assertArrayHasKey('confirm', $actions['generate']);
        $this->assertSame('mageos_shopping_feed/feed/generate?id=7', $actions['generate']['href']);
        $this->assertSame('mageos_shopping_feed/feed/edit?id=7', $actions['edit']['href']);
    }
}
