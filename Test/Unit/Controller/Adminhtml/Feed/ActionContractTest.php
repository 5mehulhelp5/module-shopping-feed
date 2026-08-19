<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Controller\Adminhtml\Feed;

use Magento\Framework\App\Action\HttpPostActionInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ActionContractTest extends TestCase
{
    public function testGridDataSourceRequiresFeedGridPermission(): void
    {
        $path = dirname(__DIR__, 5) . '/view/adminhtml/ui_component/mageos_shopping_feed_grid.xml';
        $document = new \DOMDocument();
        $document->load($path);
        $xpath = new \DOMXPath($document);

        $resources = $xpath->query(
            '//dataSource[@name="mageos_shopping_feed_grid_data_source"]' .
            '//item[@name="config"]/item[@name="aclResource"]'
        );

        $this->assertSame(1, $resources->length);
        $this->assertSame('MageOS_ShoppingFeed::grid', trim($resources->item(0)->textContent));
    }

    /**
     * @dataProvider aclResourceProvider
     */
    #[DataProvider('aclResourceProvider')]
    public function testEveryAdminActionDeclaresItsLeastPrivilegeResource(
        string $action,
        string $expectedResource
    ): void {
        $reflection = new ReflectionClass($action);

        $this->assertTrue($reflection->hasConstant('ADMIN_RESOURCE'));
        $this->assertSame($expectedResource, $reflection->getConstant('ADMIN_RESOURCE'));
    }

    public static function aclResourceProvider(): array
    {
        return [
            'delete' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Delete::class, 'MageOS_ShoppingFeed::delete'],
            'edit' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Edit::class, 'MageOS_ShoppingFeed::save'],
            'generate' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Generate::class, 'MageOS_ShoppingFeed::generate'],
            'import' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Import::class, 'MageOS_ShoppingFeed::grid'],
            'index' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Index::class, 'MageOS_ShoppingFeed::grid'],
            'mass clone' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\MassClone::class, 'MageOS_ShoppingFeed::save'],
            'mass delete' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\MassDelete::class, 'MageOS_ShoppingFeed::delete'],
            'mass disable' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\MassDisable::class, 'MageOS_ShoppingFeed::save'],
            'mass enable' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\MassEnable::class, 'MageOS_ShoppingFeed::save'],
            'new' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\NewAction::class, 'MageOS_ShoppingFeed::save'],
            'save' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Save::class, 'MageOS_ShoppingFeed::save'],
            'suggest categories' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\SuggestCategories::class, 'MageOS_ShoppingFeed::save'],
            'test' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Test::class, 'MageOS_ShoppingFeed::grid'],
            'view log' => [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Viewlog::class, 'MageOS_ShoppingFeed::grid'],
        ];
    }

    /**
     * @dataProvider mutatingActionProvider
     */
    #[DataProvider('mutatingActionProvider')]
    public function testMutatingAdminActionsOnlyAcceptPost(string $action): void
    {
        $this->assertContains(HttpPostActionInterface::class, class_implements($action));
    }

    public static function mutatingActionProvider(): array
    {
        return [
            [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Delete::class],
            [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Generate::class],
            [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\MassClone::class],
            [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\MassDelete::class],
            [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\MassDisable::class],
            [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\MassEnable::class],
            [\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Save::class],
        ];
    }
}
