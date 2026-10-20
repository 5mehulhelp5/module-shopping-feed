<?php
declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Block\Adminhtml\Feed\Form;

use MageOS\ShoppingFeed\Block\Adminhtml\Feed\Form\TestButton;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class TestButtonTest extends TestCase
{
    public function testPreviewButtonRequiresGeneratePermission(): void
    {
        $registry = $this->createMock(Registry::class);
        $registry->method('registry')->with('feed')->willReturn(new \Magento\Framework\DataObject(['id' => 17]));
        $authorization = $this->createMock(AuthorizationInterface::class);
        $authorization->expects(self::once())->method('isAllowed')
            ->with('MageOS_ShoppingFeed::generate')->willReturn(false);
        $button = new TestButton(
            $this->createMock(\Magento\Backend\Model\UrlInterface::class),
            $registry,
            $authorization
        );
        self::assertSame([], $button->getButtonData());
    }
}
