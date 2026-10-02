<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use PHPUnit\Framework\Attributes\DataProvider;
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

        $authorization = $this->createMock(AuthorizationInterface::class);
        $authorization->method('isAllowed')->willReturn(true);
        $column = new FeedActions(
            $this->createMock(ContextInterface::class),
            $this->createMock(UiComponentFactory::class),
            $urlBuilder,
            $authorization,
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

    #[DataProvider('permissions')]
    public function testRowActionsFollowIndependentPermissions(bool $save, bool $generate): void
    {
        $authorization = $this->createMock(AuthorizationInterface::class);
        $authorization->method('isAllowed')->willReturnCallback(
            static fn (string $resource): bool => match ($resource) {
                'MageOS_ShoppingFeed::save' => $save,
                'MageOS_ShoppingFeed::generate' => $generate,
                default => false,
            }
        );
        $column = new FeedActions(
            $this->createMock(ContextInterface::class),
            $this->createMock(UiComponentFactory::class),
            $this->createMock(UrlInterface::class),
            $authorization,
            [],
            ['name' => 'actions']
        );
        $source = ['data' => ['items' => [['id' => 7], ['id' => 8]]]];
        foreach ($column->prepareDataSource($source)['data']['items'] as $item) {
            self::assertSame($save, isset($item['actions']['edit']));
            self::assertSame($generate, isset($item['actions']['generate']));
            self::assertArrayHasKey('test', $item['actions']);
            self::assertArrayHasKey('viewlog', $item['actions']);
        }
    }

    public static function permissions(): array
    {
        return [[false, false], [true, false], [false, true], [true, true]];
    }

}
