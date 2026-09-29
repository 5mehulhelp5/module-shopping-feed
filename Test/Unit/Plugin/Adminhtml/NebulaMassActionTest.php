<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Test\Unit\Plugin\Adminhtml;

use MageOS\ShoppingFeed\Plugin\Adminhtml\NebulaMassAction;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class NebulaMassActionTest extends TestCase
{
    private function request($ids, string $route = 'mageos_shopping_feed'): Http
    {
        $request = $this->createMock(Http::class);
        $request->method('getRouteName')->willReturn($route);
        $request->method('getControllerName')->willReturn('feed');
        $request->method('getActionName')->willReturn('massDelete');
        $request->method('isPost')->willReturn(true);
        $request->method('getParam')->with('ids')->willReturn($ids);
        return $request;
    }

    public function testExplicitSelectionCannotExpandToAllFeeds(): void
    {
        $collection = $this->createMock(AbstractDb::class);
        $collection->method('getIdFieldName')->willReturn('id');
        $collection->expects(self::once())->method('addFieldToFilter')
            ->with('id', ['in' => ['7', '11']])->willReturnSelf();
        $plugin = new NebulaMassAction($this->request(['7', '11']));
        self::assertSame($collection, $plugin->aroundGetCollection(
            $this->createMock(Filter::class), static function () { self::fail('Must not use select-all filter.'); }, $collection
        ));
    }

    public function testStandardSelectionAndOtherRoutesStillUseMagento(): void
    {
        foreach ([[null, 'mageos_shopping_feed'], [['7'], 'catalog']] as [$ids, $route]) {
            $collection = $this->createMock(AbstractDb::class);
            $called = false;
            $result = (new NebulaMassAction($this->request($ids, $route)))->aroundGetCollection(
                $this->createMock(Filter::class), function ($argument) use (&$called, $collection) {
                    $called = true;
                    self::assertSame($collection, $argument);
                    return $argument;
                }, $collection
            );
            self::assertTrue($called);
            self::assertSame($collection, $result);
        }
    }

    public function testMalformedAndEmptySelectionsFailClosed(): void
    {
        foreach ([[], '7', ['0'], ['-1'], ['1 OR 1=1'], [['7']], ['7.0'], ['01'], [true], array_fill(0, 201, '7')] as $ids) {
            $collection = $this->createMock(AbstractDb::class);
            $collection->expects(self::never())->method('addFieldToFilter');
            try {
                (new NebulaMassAction($this->request($ids)))->aroundGetCollection(
                    $this->createMock(Filter::class), static function () { self::fail('Must fail closed.'); }, $collection
                );
                self::fail('Accepted invalid selection.');
            } catch (LocalizedException $e) {
                self::assertStringContainsString('valid feed IDs', $e->getMessage());
            }
        }
    }
}
