<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Test\Unit\Plugin;

use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\Store;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\ResourceModel\Feed\Collection;
use MageOS\ShoppingFeed\Model\ResourceModel\Feed\CollectionFactory;
use MageOS\ShoppingFeed\Plugin\FeedUseMicrodataUniqueByStore;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class FeedUseMicrodataUniqueByStoreTest extends TestCase
{
    public function testTheConflictMessageNamesTheStoreView(): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->method('addFieldToFilter')->willReturnSelf();
        $collection->method('count')->willReturn(1);
        $factory = $this->createMock(CollectionFactory::class);
        $factory->method('create')->willReturn($collection);

        $messages = [];
        $manager = $this->createMock(ManagerInterface::class);
        $manager->method('addWarningMessage')->willReturnCallback(
            function ($message) use (&$messages, $manager) {
                $messages['warning'] = (string) $message;
                return $manager;
            }
        );
        $manager->method('addSuccessMessage')->willReturnCallback(
            function ($message) use (&$messages, $manager) {
                $messages['success'] = (string) $message;
                return $manager;
            }
        );

        $store = $this->createMock(Store::class);
        $store->method('getId')->willReturn(1);
        $store->method('getName')->willReturn('Default Store View');
        $feed = $this->createMock(Feed::class);
        $feed->method('getData')->willReturnMap([['use_microdata', null, '1']]);
        $feed->method('getStore')->willReturn($store);
        $feed->method('getId')->willReturn(7);
        $disabled = null;
        $feed->method('setData')->willReturnCallback(
            function ($key, $value = null) use (&$disabled, $feed) {
                $disabled = [$key, $value];
                return $feed;
            }
        );

        (new FeedUseMicrodataUniqueByStore($factory, $manager))->beforeSave($feed);

        $this->assertSame(['use_microdata', 0], $disabled);
        $this->assertSame(
            'To set microdata for "Default Store View" to use this feed\'s mapping, edit the other feeds and set'
            . ' "Use for microdata" = No',
            $messages['success']
        );
        $this->assertStringNotContainsString('{store}', $messages['success']);
    }
}
