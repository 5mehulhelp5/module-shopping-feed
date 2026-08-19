<?php

namespace MageOS\ShoppingFeed\Test\Unit\Block\Product\View\Configurable;

use MageOS\ShoppingFeed\Block\Product\View\Configurable\Selection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class SelectionTest extends TestCase
{
    /**
     * @var Selection
     */
    private $block;

    /**
     * @var array
     */
    private $products = [];

    protected function setUp(): void
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $product = $this->createMock('Magento\Catalog\Model\Product');
        $product->method('getTypeId')->willReturn(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        );
        $typeInstance = $this->createMock('Magento\Catalog\Model\Product\Type\AbstractType');
        $typeInstance->method('getStoreFilter')->willReturnSelf();
        $product->method('getTypeInstance')->willReturn($typeInstance);

        foreach ([123 => 'abc', 222 => 'mnp', 321 => 'xyz'] as $id => $sku) {
            $child = $this->createMock('Magento\Catalog\Model\Product');
            $child->method('getId')->willReturn($id);
            $child->method('getSku')->willReturn($sku);
            $this->products[] = $child;
        }

        $scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config')
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();
        $scopeConfig->method('getValue')->willReturnMap([
            [Selection::GOOGLE_ADS_DESTINATION_ID_PATH, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, ' AW-123456 '],
            [Selection::DYNAMIC_REMARKETING_ENABLED_PATH, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, '1'],
        ]);

        $registry = $this->createMock('Magento\Framework\Registry');
        $registry->method('registry')->with('product')->willReturn($product);
        $context = $this->getMockBuilder('Magento\Catalog\Block\Product\Context')
            ->disableOriginalConstructor()
            ->onlyMethods(['getRegistry', 'getScopeConfig'])
            ->getMock();
        $context->method('getRegistry')->willReturn($registry);
        $context->method('getScopeConfig')->willReturn($scopeConfig);

        $configurableType = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
            ->disableOriginalConstructor()
            ->onlyMethods(['getUsedProducts'])
            ->getMock();
        $configurableType->method('getUsedProducts')->with($product, null)->willReturn($this->products);

        $this->block = $objectHelper->getObject(Selection::class, [
            'context' => $context,
            'configurableProductType' => $configurableType,
            'localeFormat' => $this->createMock('Magento\Framework\Locale\Format'),
            'customerSession' => $this->createMock('Magento\Customer\Model\Session'),
            'variationPrices' => $this->createMock(
                'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices'
            ),
        ]);
    }

    public function testReturnsModernGoogleAdsSettings(): void
    {
        $this->assertTrue($this->block->isDynamicRemarketingEnabled());
        $this->assertSame('AW-123456', $this->block->getGoogleAdsDestinationId());
    }

    public function testReturnsAssociatedProducts(): void
    {
        $this->assertSame($this->products, $this->block->getProducts());
    }
}
