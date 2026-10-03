<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Ui\Component\Listing;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\Action;
use MageOS\ShoppingFeed\Ui\Component\Listing\MassAction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class MassActionTest extends TestCase
{
    /** @dataProvider permissions */
    #[DataProvider('permissions')]
    public function testConfiguredActionsFollowIndependentPermissions(bool $save, bool $delete): void
    {
        $component = $this->component($save, $delete);
        $component->prepare();
        $config = $component->getConfiguration();
        $expected = array_merge($save ? ['enable', 'disable', 'clone'] : [], $delete ? ['delete'] : []);
        self::assertSame($expected, array_column($config['actions'], 'type'));
        self::assertSame($expected === [], $config['componentDisabled'] ?? false);
        self::assertSame(array_values($config['actions']), $config['actions']);
    }

    public function testAdditionalActionsKeepTheirOwnPermissionsAndDisabledState(): void
    {
        $component = $this->component(false, false);
        foreach ([
            ['type' => 'custom_read'],
            ['type' => 'custom_write', 'aclResource' => 'Vendor_Module::write'],
            ['type' => 'disabled_read', 'actionDisable' => true],
        ] as $config) {
            $component->addComponent($config['type'], new Action($component->getContext(), [], ['config' => $config]));
        }
        $component->prepare();
        self::assertSame(['custom_read'], array_column($component->getConfiguration()['actions'], 'type'));
        self::assertFalse($component->getConfiguration()['componentDisabled'] ?? false);
    }

    public static function permissions(): array
    {
        return [[false, false], [true, false], [false, true], [true, true]];
    }

    private function component(bool $save, bool $delete): MassAction
    {
        $authorization = $this->createMock(AuthorizationInterface::class);
        $authorization->method('isAllowed')->willReturnCallback(
            static fn (string $resource): bool => match ($resource) {
                'MageOS_ShoppingFeed::save' => $save,
                'MageOS_ShoppingFeed::delete' => $delete,
                default => false,
            }
        );
        $context = $this->createMock(ContextInterface::class);
        $context->method('getProcessor')->willReturn($this->createMock(Processor::class));
        $xml = simplexml_load_file(dirname(__DIR__, 5) . '/view/adminhtml/ui_component/mageos_shopping_feed_grid.xml');
        $massAction = $xml->xpath('//massaction')[0];
        self::assertSame(MassAction::class, (string)$massAction['class']);
        $children = [];
        foreach ($massAction->action as $action) {
            $config = [];
            foreach ($action->argument->item->item as $item) {
                $config[(string)$item['name']] = (string)$item;
            }
            $children[] = new Action($context, [], ['config' => $config]);
        }
        return new MassAction($context, $authorization, $children);
    }
}
