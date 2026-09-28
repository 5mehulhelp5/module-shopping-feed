<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Test\Unit\Ui\Component\Listing\Column;

use MageOS\ShoppingFeed\Ui\Component\Listing\Column\Name;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class NameTest extends TestCase
{
    public function testFeedNamesAreTextInTheStandardHtmlCell(): void
    {
        $feed=(new \ReflectionClass(\MageOS\ShoppingFeed\Model\Feed::class))->newInstanceWithoutConstructor();
        $factory=$this->createMock(\MageOS\ShoppingFeed\Model\FeedFactory::class);
        $factory->method('create')->willReturn($feed);
        $column=(new \ReflectionClass(Name::class))->newInstanceWithoutConstructor();
        $column->setData('name','name');
        (new \ReflectionProperty(Name::class,'feedFactory'))->setValue($column,$factory);
        $data=$column->prepareDataSource(['data'=>['items'=>[['id'=>7,'name'=>'<img src=x onerror="alert(1)">','use_microdata'=>1]]]]);
        $cell=$data['data']['items'][0]['name'];
        self::assertStringNotContainsString('<img',$cell);
        self::assertStringContainsString('&lt;img',$cell);
        self::assertStringContainsString('[microdata]',$cell);
    }
}
