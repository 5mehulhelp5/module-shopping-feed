<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Block\Adminhtml\Feed\Form;

use MageOS\ShoppingFeed\Block\Adminhtml\Feed\Form\RunTestButton;
use Magento\Ui\Component\Control\Button;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class RunTestButtonTest extends TestCase
{
    public function testPreviewActionDoesNotAlsoNavigateToTheDefaultAdminUrl(): void
    {
        $renderer = $this->getMockBuilder(Button::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrl'])
            ->getMock();
        $renderer->method('getUrl')->willReturn('https://example.test/admin/mageos_shopping_feed/index/index/');
        $renderer->setData((new RunTestButton())->getButtonData());

        self::assertEmpty($renderer->getOnClick());
    }
}
