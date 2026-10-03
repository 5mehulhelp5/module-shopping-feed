<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Block\Adminhtml;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\ToolbarInterface;
use Magento\Framework\AuthorizationInterface;
use MageOS\ShoppingFeed\Block\Adminhtml\Feed;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class FeedTest extends TestCase
{
    /** @dataProvider permissions */
    #[DataProvider('permissions')]
    public function testCreateButtonRequiresSavePermission(bool $allowed): void
    {
        $authorization = $this->createMock(AuthorizationInterface::class);
        $authorization->method('isAllowed')->with('MageOS_ShoppingFeed::save')->willReturn($allowed);
        $buttons = $this->createMock(ButtonList::class);
        $buttons->expects($allowed ? self::once() : self::never())->method('add')->with('add_new', self::anything());
        $block = $this->getMockBuilder(Feed::class)->disableOriginalConstructor()
            ->onlyMethods(['_getAddFeedButtonOptions'])->getMock();
        $block->method('_getAddFeedButtonOptions')->willReturn([]);
        foreach (['_authorization' => $authorization, 'buttonList' => $buttons,
            'toolbar' => $this->createMock(ToolbarInterface::class)] as $property => $value) {
            (new \ReflectionProperty(Feed::class, $property))->setValue($block, $value);
        }
        (new \ReflectionMethod(Feed::class, '_prepareLayout'))->invoke($block);
    }

    public static function permissions(): array
    {
        return [[false], [true]];
    }
}
