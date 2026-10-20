<?php
declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Block\Adminhtml;

use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class PromotionCouponTest extends TestCase
{
    public function testCouponTextIsEscapedExactlyOnce(): void
    {
        $coupon = 'SAVE&<strong>10</strong>"';
        $escaper = new Escaper();
        (new \ReflectionProperty(Escaper::class, 'logger'))
            ->setValue($escaper, $this->createMock(\Psr\Log\LoggerInterface::class));
        (new \ReflectionProperty(Escaper::class, 'escaper'))->setValue($escaper, new \Magento\Framework\ZendEscaper());
        (new \ReflectionProperty(Escaper::class, 'translateInline'))
            ->setValue($escaper, $this->createMock(\Magento\Framework\Translate\InlineInterface::class));
        $block = new class($coupon, $escaper) extends DataObject {
            public function __construct(private string $coupon, private Escaper $escaper)
            {
                parent::__construct(['control_name' => 'promotions']);
            }

            public function getElement(): DataObject
            {
                return new DataObject(['name' => 'promotions', 'html_id' => 'promotions', 'class' => '']);
            }

            /** @return list<DataObject> */
            public function getMagentoCartRules(): array
            {
                return [new DataObject(['id' => 1, 'name' => 'Test rule', 'code' => $this->coupon])];
            }

            public function getPromotionData(string $key, mixed $default = null): mixed
            {
                return $default ?? [];
            }

            /**
             * @param DataObject $rule
             * @param array $row
             * @phpstan-param array<string, mixed> $row
             * @return array<string, mixed>
             */
            public function getPromotionRow(DataObject $rule, array $row): array
            {
                return ['include' => false, 'title' => '', 'date' => ['from' => '', 'to' => ''],
                    'display' => ['from' => '', 'to' => '']];
            }

            public function isShippingWithoutCoupon(DataObject $rule): bool
            {
                return false;
            }

            public function helper(string $name): object
            {
                return new class {
                    public function jsonEncode(mixed $value): string
                    {
                        return json_encode($value, JSON_THROW_ON_ERROR);
                    }
                };
            }

            public function render(string $path): string
            {
                $block = $this;
                $escaper = $this->escaper;
                ob_start();
                try {
                    include $path;
                    return (string)ob_get_contents();
                } finally {
                    ob_end_clean();
                }
            }
        };
        $html = $block->render(dirname(__DIR__, 4) . '/view/adminhtml/templates/feed/edit/tab/promotions/widget.phtml');
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML($html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $cells = (new \DOMXPath($document))->query('//tbody/tr/td[last()]');
        self::assertInstanceOf(\DOMNodeList::class, $cells);
        $cell = $cells->item(0);
        self::assertInstanceOf(\DOMElement::class, $cell);
        self::assertSame($coupon, $cell->textContent);
        self::assertSame(0, $cell->getElementsByTagName('strong')->length);
    }
}
