<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Test\Unit\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use MageOS\ShoppingFeed\Model\ResourceModel\Feed\Collection;
use MageOS\ShoppingFeed\Model\ResourceModel\Feed\CollectionFactory;
use MageOS\ShoppingFeed\Plugin\MicrodataRemoverPlugin;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class MicrodataRemoverPluginTest extends TestCase
{
    public static function settings(): array
    {
        return [[true, false, true], [true, true, false], [false, true, true]];
    }

    /** @dataProvider settings */
    #[DataProvider('settings')]
    public function testNativeSchemaRequiresAnExplicitReplacementFeed(bool $enabled, bool $selected, bool $expected): void
    {
        $config = $this->createMock(ScopeConfigInterface::class);
        $config->method('getValue')->willReturn($enabled);
        $config->method('isSetFlag')->willReturn($enabled);
        $store = $this->createMock(Store::class);
        $store->method('getId')->willReturn(2);
        $stores = $this->createMock(StoreManagerInterface::class);
        $stores->method('getStore')->willReturn($store);
        $collection = $this->createMock(Collection::class);
        $collection->method('addFieldToFilter')->willReturnSelf();
        $collection->method('getSize')->willReturn($selected ? 1 : 0);
        $factory = $this->createMock(CollectionFactory::class);
        $factory->method('create')->willReturn($collection);
        $plugin = new MicrodataRemoverPlugin($config, $factory, $stores);
        $subject = $this->getMockBuilder(Amount::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $subject->setData('schema', true);
        $plugin->beforeFetchView($subject, 'default.phtml');
        $this->assertSame($expected, $subject->getData('schema'));
    }
}
