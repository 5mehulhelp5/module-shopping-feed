<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Builder;
use MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Generate;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Generator\Queue;
use MageOS\ShoppingFeed\Model\Generator\QueueFactory;
use MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue\Collection;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class GenerateTest extends TestCase
{
    public function testManualGenerationUsesQueueInvariantEntryPoint(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getParams')->willReturn(['id' => 17]);
        $messageManager = $this->createMock(ManagerInterface::class);
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('setPath')->willReturnSelf();
        $redirectFactory = $this->createMock(RedirectFactory::class);
        $redirectFactory->method('create')->willReturn($redirect);

        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($request);
        $context->method('getMessageManager')->willReturn($messageManager);
        $context->method('getResultRedirectFactory')->willReturn($redirectFactory);

        $feed = $this->getMockBuilder(Feed::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSchedules', 'saveStatus', 'getId'])
            ->getMock();
        $feed->method('getId')->willReturn(17);
        $feed->method('getSchedules')->willReturn([]);
        $feed->expects($this->once())->method('saveStatus');

        $builder = $this->createMock(Builder::class);
        $builder->method('build')->willReturn($feed);

        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where'])
            ->getMock();
        $select->method('where')->willReturnSelf();
        $queueCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSelect', 'walk'])
            ->getMock();
        $queueCollection->method('getSelect')->willReturn($select);

        $queue = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['add'])
            ->getMock();
        $queue->expects($this->once())->method('add')->with($feed)->willReturnSelf();
        $queueFactory = $this->createMock(QueueFactory::class);
        $queueFactory->method('create')->willReturn($queue);

        $controller = new Generate(
            $context,
            $builder,
            $queueCollection,
            $queueFactory,
            $this->createMock(Registry::class)
        );

        $this->assertSame($redirect, $controller->execute());
    }
}
