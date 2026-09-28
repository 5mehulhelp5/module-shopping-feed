<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Test\Unit\Plugin\Adminhtml;

use MageOS\ShoppingFeed\Plugin\Adminhtml\FeedEditorTheme;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Module\Manager;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class FeedEditorThemeTest extends TestCase
{
    public function testOnlyFeedDetailScreensUseTheStandardEditor(): void
    {
        foreach ([['edit',true,'adminhtml','Magento/backend'],['new',true,'adminhtml','Magento/backend'],
            ['test',true,'adminhtml','Magento/backend'],['viewlog',true,'adminhtml','Magento/backend'],
            ['index',true,'adminhtml','Qoliber/Nebula'],['edit',false,'adminhtml','Qoliber/Nebula'],
            ['edit',true,'frontend','Qoliber/Nebula']] as [$action,$enabled,$area,$expected]) {
            $request = $this->createMock(Http::class);
            $request->method('getRouteName')->willReturn('mageos_shopping_feed');
            $request->method('getControllerName')->willReturn('feed');
            $request->method('getActionName')->willReturn($action);
            $modules = $this->createMock(Manager::class);
            $modules->method('isEnabled')->with('Qoliber_Nebula')->willReturn($enabled);
            $plugin = new FeedEditorTheme($request, $modules);
            self::assertSame([$expected, $area], $plugin->beforeSetDesignTheme(new \stdClass(), 'Qoliber/Nebula', $area));
        }
    }

    public function testAnotherModulesEditScreenKeepsItsTheme(): void
    {
        $request = $this->createMock(Http::class);
        $request->method('getRouteName')->willReturn('catalog');
        $request->method('getControllerName')->willReturn('product');
        $request->method('getActionName')->willReturn('edit');
        $modules = $this->createMock(Manager::class);
        $modules->method('isEnabled')->willReturn(true);
        $plugin = new FeedEditorTheme($request, $modules);
        self::assertSame(['Qoliber/Nebula', 'adminhtml'], $plugin->beforeSetDesignTheme(new \stdClass(), 'Qoliber/Nebula', 'adminhtml'));
    }
}
