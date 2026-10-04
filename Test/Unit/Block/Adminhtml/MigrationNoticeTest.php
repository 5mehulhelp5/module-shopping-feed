<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Block\Adminhtml;

use MageOS\ShoppingFeed\Block\Adminhtml\Feed\MigrationNotice;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Module\Manager;
use PHPUnit\Framework\TestCase;

class MigrationNoticeTest extends TestCase
{
    private function block(bool $installed, bool $authorized, ?string $legacy, ?bool $table): MigrationNotice
    {
        $block = (new \ReflectionClass(MigrationNotice::class))->newInstanceWithoutConstructor();
        $modules = $this->createMock(Manager::class);
        $modules->expects(self::once())->method('isEnabled')->with('RocketWeb_ShoppingFeedMigration')->willReturn($installed);
        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects($installed ? self::once() : self::never())->method('isAllowed')
            ->with('RocketWeb_ShoppingFeedMigration::migration')->willReturn($authorized);
        $registrar = $this->createMock(ComponentRegistrar::class);
        $registrar->expects($installed ? self::never() : self::once())->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'RocketWeb_ShoppingFeeds')->willReturn($legacy);
        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($table === null ? self::never() : self::once())->method('getTableName')
            ->with('rw_shoppingfeeds_feed')->willReturn('store_prefix_rw_shoppingfeeds_feed');
        if ($table !== null) {
            $db = $this->createMock(AdapterInterface::class);
            $db->expects(self::once())->method('isTableExists')->with('store_prefix_rw_shoppingfeeds_feed')->willReturn($table);
            $resource->expects(self::once())->method('getConnection')->willReturn($db);
        }
        foreach (['modules' => $modules, 'authorization' => $auth, 'registrar' => $registrar, 'resource' => $resource] as $key => $value) {
            (new \ReflectionProperty(MigrationNotice::class, $key))->setValue($block, $value);
        }
        return $block;
    }

    public function testAuthorizedInstalledModuleLinksToMigration(): void
    {
        self::assertSame('installed', $this->block(true, true, null, null)->getNoticeState());
    }
    public function testUnauthorizedUserGetsNoMigrationLink(): void
    {
        self::assertSame('', $this->block(true, false, null, null)->getNoticeState());
    }
    public function testRegisteredLegacyModuleNeedsNoLegacyClassOrDatabaseLookup(): void
    {
        self::assertSame('legacy_module', $this->block(false, false, '/legacy', null)->getNoticeState());
    }
    public function testRemainingPrefixedTablesAreDistinguished(): void
    {
        self::assertSame('legacy_tables', $this->block(false, false, null, true)->getNoticeState());
    }
    public function testCleanInstallationHasNoNotice(): void
    {
        self::assertSame('', $this->block(false, false, null, false)->getNoticeState());
    }
}
