<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Block\Adminhtml;

use Magento\Framework\Escaper;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class TabNoticeTest extends TestCase
{
    public function testNoticeRendersSafeLinksAndFormatting(): void
    {
        $escaper = new Escaper();
        (new \ReflectionProperty(Escaper::class, 'logger'))->setValue($escaper, $this->createMock(\Psr\Log\LoggerInterface::class));
        (new \ReflectionProperty(Escaper::class, 'escaper'))->setValue($escaper, new \Magento\Framework\ZendEscaper());
        (new \ReflectionProperty(Escaper::class, 'translateInline'))->setValue(
            $escaper, $this->createMock(\Magento\Framework\Translate\InlineInterface::class)
        );
        $block = new class ($escaper) {
            public function __construct(private Escaper $escaper)
            {
            }
            public function getTabNotice(): string
            {
                return '<strong>Products</strong><br /><a href="#feed_tabs_filters" onclick="alert(1)">Filters</a>'
                    . '<script>alert(2)</script><a href="javascript:alert(3)">Unsafe</a>';
            }
            public function escapeHtml($value, $allowed = null): string
            {
                return $this->escaper->escapeHtml($value, $allowed);
            }
            public function getFormHtml(): string
            {
                return '';
            }
            public function getChildHtml($alias): string
            {
                return '';
            }
        };
        ob_start();
        require dirname(__DIR__, 4) . '/view/adminhtml/templates/feed/edit/tab.phtml';
        $html = ob_get_clean();
        $document = new \DOMDocument();
        $document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//a[@href="#feed_tabs_filters"]')->length);
        $this->assertSame(1, $xpath->query('//strong')->length);
        $this->assertSame(1, $xpath->query('//br')->length);
        $this->assertSame(0, $xpath->query('//script | //*[@onclick] | //a[starts-with(@href,"javascript:")]')->length);
    }
}
