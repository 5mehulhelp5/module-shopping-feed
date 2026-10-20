<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Helper;

use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;
use MageOS\ShoppingFeed\Model\Product\Helper\Catalog;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CatalogTest extends TestCase
{
    /** @dataProvider displayTypes */
    #[DataProvider('displayTypes')]
    public function testTaxCalculationFollowsTheStorePriceDisplayType(int $displayType, float $expected): void
    {
        $catalog = $this->catalog($displayType);

        $this->assertSame(
            $expected,
            $catalog->getTaxPriceIncludingTax($this->createMock(Product::class), 100.0, null, 3, null, false, false)
        );
    }

    /**
     * @return array<string, array{int, float}>
     */
    public static function displayTypes(): array
    {
        return [
            'excluding tax' => [Config::DISPLAY_TYPE_EXCLUDING_TAX, 100.0],
            'both' => [Config::DISPLAY_TYPE_BOTH, 100.0],
            'including tax' => [Config::DISPLAY_TYPE_INCLUDING_TAX, 120.0],
        ];
    }

    public function testExplicitInclusionStillReturnsThePriceWithTax(): void
    {
        $catalog = $this->catalog(Config::DISPLAY_TYPE_EXCLUDING_TAX);

        $this->assertSame(
            120.0,
            $catalog->getTaxPriceIncludingTax($this->createMock(Product::class), 100.0, true, 3, null, false, false)
        );
    }

    private function catalog(int $displayType): Catalog
    {
        $catalog = (new \ReflectionClass(Catalog::class))->newInstanceWithoutConstructor();

        $store = $this->createMock(Store::class);
        $store->method('getId')->willReturn(1);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $taxConfig = $this->createMock(Config::class);
        $taxConfig->method('getPriceDisplayType')->willReturn($displayType);

        $taxClassKey = $this->createMock(TaxClassKeyInterface::class);
        $taxClassKey->method('setType')->willReturnSelf();
        $taxClassKey->method('setValue')->willReturnSelf();
        $taxClassKeyFactory = $this->createMock(TaxClassKeyInterfaceFactory::class);
        $taxClassKeyFactory->method('create')->willReturn($taxClassKey);

        $item = $this->createMock(QuoteDetailsItemInterface::class);
        $itemSetters = [
            'setQuantity',
            'setCode',
            'setShortDescription',
            'setTaxClassKey',
            'setIsTaxIncluded',
            'setType',
            'setUnitPrice',
        ];
        foreach ($itemSetters as $setter) {
            $item->method($setter)->willReturnSelf();
        }
        $itemFactory = $this->createMock(QuoteDetailsItemInterfaceFactory::class);
        $itemFactory->method('create')->willReturn($item);

        $quoteDetails = $this->createMock(QuoteDetailsInterface::class);
        $quoteSetters = [
            'setShippingAddress',
            'setBillingAddress',
            'setCustomerTaxClassKey',
            'setItems',
            'setCustomerId',
        ];
        foreach ($quoteSetters as $setter) {
            $quoteDetails->method($setter)->willReturnSelf();
        }
        $quoteDetailsFactory = $this->createMock(QuoteDetailsInterfaceFactory::class);
        $quoteDetailsFactory->method('create')->willReturn($quoteDetails);

        $taxItem = $this->createMock(TaxDetailsItemInterface::class);
        $taxItem->method('getPrice')->willReturn(100.0);
        $taxItem->method('getPriceInclTax')->willReturn(120.0);
        $taxDetails = $this->createMock(TaxDetailsInterface::class);
        $taxDetails->method('getItems')->willReturn([$taxItem]);
        $taxCalculation = $this->createMock(TaxCalculationInterface::class);
        $taxCalculation->method('calculateTax')->willReturn($taxDetails);

        $properties = [
            '_storeManager' => $storeManager,
            '_taxConfig' => $taxConfig,
            '_taxClassKeyFactory' => $taxClassKeyFactory,
            '_quoteDetailsItemFactory' => $itemFactory,
            '_quoteDetailsFactory' => $quoteDetailsFactory,
            '_taxCalculationService' => $taxCalculation,
            '_customerSession' => $this->createMock(Session::class),
            'priceCurrency' => $this->createMock(PriceCurrencyInterface::class),
        ];
        foreach ($properties as $name => $value) {
            (new \ReflectionProperty(Data::class, $name))->setValue($catalog, $value);
        }

        return $catalog;
    }
}
