<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Cron;

use ArrayIterator;
use DateTime;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MageOS\ShoppingFeed\Cron\Schedule as ScheduleCron;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Feed\Schedule;
use MageOS\ShoppingFeed\Model\FeedFactory;
use MageOS\ShoppingFeed\Model\Generator\BatchFactory;
use MageOS\ShoppingFeed\Model\Generator\Queue;
use MageOS\ShoppingFeed\Model\ResourceModel\Feed\Schedule\Collection as ScheduleCollection;
use MageOS\ShoppingFeed\Model\ResourceModel\Feed\Schedule\CollectionFactory as ScheduleCollectionFactory;
use MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue\Collection as QueueCollection;
use MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue\CollectionFactory as QueueCollectionFactory;
use MageOS\ShoppingFeed\Test\Unit\CompatibilityTestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ScheduleTest extends CompatibilityTestCase
{
    public function testScheduledGenerationUsesQueueInvariantEntryPoint(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->with(ScheduleCron::XML_PATH_ENABLED)->willReturn(true);

        $schedule = $this->createCompatibleMock(
            Schedule::class,
            ['save', 'getFeedId', 'getBatchMode', 'setProcessedAt']
        );
        $schedule->method('getFeedId')->willReturn(17);
        $schedule->method('getBatchMode')->willReturn(false);
        $schedule->method('setProcessedAt')->willReturnSelf();
        $schedule->expects($this->once())->method('save');

        $scheduleCollection = $this->getMockBuilder(ScheduleCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setHourFilter', 'setDateFilter', 'getIterator'])
            ->getMock();
        $scheduleCollection->method('setHourFilter')->willReturnSelf();
        $scheduleCollection->method('setDateFilter')->willReturnSelf();
        $scheduleCollection->method('getIterator')->willReturn(new ArrayIterator([$schedule]));
        $scheduleCollectionFactory = $this->createMock(ScheduleCollectionFactory::class);
        $scheduleCollectionFactory->method('create')->willReturn($scheduleCollection);

        $feed = $this->getMockBuilder(Feed::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'saveStatus'])
            ->getMock();
        $feed->method('load')->with(17)->willReturnSelf();
        $feed->expects($this->once())->method('saveStatus');
        $feedFactory = $this->createMock(FeedFactory::class);
        $feedFactory->method('create')->willReturn($feed);

        $queue = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'add'])
            ->getMock();
        $queue->method('getId')->willReturn(null);
        $queue->expects($this->once())->method('add')->with($feed, $schedule)->willReturnSelf();
        $queueCollection = $this->getMockBuilder(QueueCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQueue'])
            ->getMock();
        $queueCollection->method('getQueue')->with(17)->willReturn($queue);
        $queueCollectionFactory = $this->createMock(QueueCollectionFactory::class);
        $queueCollectionFactory->method('create')->willReturn($queueCollection);

        $timezone = $this->createMock(TimezoneInterface::class);
        $timezone->method('date')->willReturn(new DateTime('2026-08-15 09:00:00'));

        $subject = new ScheduleCron(
            $this->createMock(BatchFactory::class),
            $feedFactory,
            $scheduleCollectionFactory,
            $queueCollectionFactory,
            $scopeConfig,
            $timezone
        );

        $subject->execute();

        $this->assertSame(1, $subject->getCounter());
    }
}
