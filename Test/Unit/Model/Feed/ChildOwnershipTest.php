<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Test\Unit\Model\Feed;

use MageOS\ShoppingFeed\Model\Feed;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ChildOwnershipTest extends TestCase
{
    public function testExistingUploadMayOmitDeleteFlag(): void
    {
        $feed = (new \ReflectionClass(Feed::class))->newInstanceWithoutConstructor();
        $feed->setId(8);
        $child = $this->getMockBuilder(\MageOS\ShoppingFeed\Model\Feed\Upload::class)
            ->disableOriginalConstructor()->onlyMethods(['load','save','delete'])->getMock();
        $child->setId(12)->setData('feed_id', 8);
        $child->method('load')->willReturnSelf();
        $child->expects(self::once())->method('save');
        $child->expects(self::never())->method('delete');
        $factory = $this->createMock(\MageOS\ShoppingFeed\Model\Feed\UploadFactory::class);
        $factory->method('create')->willReturn($child);
        (new \ReflectionProperty(Feed::class, 'uploadFactory'))->setValue($feed, $factory);
        $feed->setData('uploads', [['id'=>12,'mode'=>'sftp','host'=>'example.test','port'=>22,
            'username'=>'fixture','password'=>'synthetic','gzip'=>0,'path'=>'']]);
        $feed->saveUploads();
    }

    public static function foreignRows(): array
    {
        return [
            ['uploads', 'Upload', false], ['uploads', 'Upload', true],
            ['schedules', 'Schedule', false], ['schedules', 'Schedule', true],
        ];
    }

    /** @dataProvider foreignRows */
    #[DataProvider('foreignRows')]
    public function testOtherFeedsChildCannotBeChanged(string $rows, string $type, bool $delete): void
    {
        $feed = (new \ReflectionClass(Feed::class))->newInstanceWithoutConstructor();
        $feed->setId(8);
        $childClass = 'MageOS\\ShoppingFeed\\Model\\Feed\\' . $type;
        $child = $this->getMockBuilder($childClass)->disableOriginalConstructor()
            ->onlyMethods(['load', 'save', 'delete'])->getMock();
        $child->setId(12)->addData(['feed_id' => 9, 'start_at' => 1, 'processed_at' => '2026-09-24 00:00:00']);
        $child->method('load')->willReturnSelf();
        $child->expects($this->never())->method('save');
        $child->expects($this->never())->method('delete');
        $factory = $this->createMock($childClass . 'Factory');
        $factory->method('create')->willReturn($child);
        (new \ReflectionProperty(Feed::class, lcfirst($type) . 'Factory'))->setValue($feed, $factory);
        $feed->setData($rows, [[
            'id' => 12, 'delete' => $delete, 'start_at' => 1,
            'mode' => 'sftp', 'host' => 'example.test', 'port' => 22, 'username' => 'fixture',
            'password' => 'synthetic', 'gzip' => 0, 'path' => '',
        ]]);
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $feed->{'save' . ucfirst($rows)}();
    }
}
