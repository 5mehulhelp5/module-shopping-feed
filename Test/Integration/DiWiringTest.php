<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Integration;

use Magento\TestFramework\Helper\Bootstrap;
use MageOS\ShoppingFeed\Cron\Process;
use MageOS\ShoppingFeed\Cron\Schedule;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Feed\OutputPath;
use MageOS\ShoppingFeed\Model\Generator;
use MageOS\ShoppingFeed\Model\Promotions\Provider;
use MageOS\ShoppingFeed\Model\Taxonomy\Type\GoogleShopping;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Smoke-test the dependency trees changed by production hardening.
 *
 * @magentoAppArea adminhtml
 */
class DiWiringTest extends TestCase
{
    /**
     * @dataProvider serviceProvider
     */
    #[DataProvider('serviceProvider')]
    public function testServiceIsInstantiableViaDi(string $service): void
    {
        $instance = Bootstrap::getObjectManager()->create($service);
        $reflection = new \ReflectionClass($instance);
        if (method_exists($reflection, 'isUninitializedLazyObject') && $reflection->isUninitializedLazyObject($instance)) {
            $reflection->initializeLazyObject($instance);
        }

        $this->assertInstanceOf($service, $instance);
    }

    public static function serviceProvider(): array
    {
        return [
            OutputPath::class => [OutputPath::class],
            Feed::class => [Feed::class],
            Generator::class => [Generator::class],
            Schedule::class => [Schedule::class],
            Process::class => [Process::class],
            Provider::class => [Provider::class],
            GoogleShopping::class => [GoogleShopping::class],
            'File column' => [\MageOS\ShoppingFeed\Ui\Component\Listing\Column\File::class],
            'Promotion file column' => [\MageOS\ShoppingFeed\Ui\Component\Listing\Column\File\Plugin::class],
            'Schedules column' => [\MageOS\ShoppingFeed\Ui\Component\Listing\Column\Schedules::class],
            'Status column' => [\MageOS\ShoppingFeed\Ui\Component\Listing\Column\Status::class],
        ];
    }
}
