<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Test\Unit\Plugin\Adminhtml;

use MageOS\ShoppingFeed\Plugin\Adminhtml\NebulaDefinition;
use MageOS\ShoppingFeed\Plugin\Adminhtml\NebulaExport;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class NebulaExportTest extends TestCase
{
    public function testOnlyFeedExportsBypassGridPagination(): void
    {
        foreach (['nebula_grid_export', 'nebula_grid_data', 'mageos_shopping_feed_feed_index'] as $action) {
            $request=$this->createMock(\Magento\Framework\App\Request\Http::class);
            $request->method('getFullActionName')->willReturn($action);
            foreach (['shopping_feed.collection','another.collection'] as $alias) {
                [$config,$params]=(new NebulaExport($request))->beforeGetData(new \stdClass(),['collection'=>$alias],['pageSize'=>20,'filters'=>['name'=>'sample']]);
                self::assertSame($action==='nebula_grid_export'&&$alias==='shopping_feed.collection'?0:20,$params['pageSize']);
                self::assertSame(['name'=>'sample'],$params['filters']);
            }
        }
    }

    public function testFilterAndPageParametersReachTheRequestAsQueryParameters(): void
    {
        $subject=new class { public function getGridId(){ return 'mageos_shopping_feed_grid'; } };
        $plugin=new NebulaDefinition($this->createMock(\Magento\Framework\AuthorizationInterface::class));
        [$route,$params]=$plugin->beforeGetUrl($subject,'nebula/grid/export',['id'=>'mageos_shopping_feed_grid','filters[name]'=>'Google','filters[status]'=>'0']);
        self::assertSame(['name'=>'Google','status'=>'0'],$params['_query']['filters']);
        self::assertArrayNotHasKey('filters[name]',$params);
        [$route,$params]=$plugin->beforeGetUrl($subject,'*/*/*',['page'=>2,'pageSize'=>10,'filters'=>null]);
        self::assertSame(['_query'=>['page'=>2,'pageSize'=>10,'filters'=>null]],$params);
    }
}
