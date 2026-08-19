<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use MageOS\ShoppingFeed\Cron\Process;
use MageOS\ShoppingFeed\Model\Feed\Source\Status;
use MageOS\ShoppingFeed\Model\Generator;
use MageOS\ShoppingFeed\Model\Generator\Queue;
use MageOS\ShoppingFeed\Model\Logger;
use MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue\Collection;
use MageOS\ShoppingFeed\Test\Unit\CompatibilityTestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ProcessTest extends CompatibilityTestCase
{
    public function testUnexpectedThrowableMarksFeedAsErrorAndAlwaysReleasesLock(): void
    {
        $generator = $this->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run', 'updateStatus'])
            ->getMock();
        $generator->method('run')->willThrowException(new \Error('generation failed'));
        $generator->expects($this->once())->method('updateStatus')->with(Status::STATUS_ERROR);

        $logger = $this->createMock(Logger::class);
        $logger->expects($this->once())->method('error')->with('generation failed');

        $subject = $this->createProcess($generator, $logger);
        $result = $subject->execute();

        $this->assertTrue($subject->released);
        $this->assertFalse($result);
    }

    public function testSuccessfulGenerationAlwaysReleasesLock(): void
    {
        $generator = $this->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run'])
            ->getMock();
        $generator->expects($this->once())->method('run');

        $subject = $this->createProcess($generator, $this->createMock(Logger::class));
        $result = $subject->execute();

        $this->assertTrue($subject->released);
        $this->assertTrue($result);
    }

    public function testReleaseLockUnlocksAndClosesHandle(): void
    {
        $subject = new Process(
            $this->createMock(DirectoryList::class),
            new File(),
            $this->createMock(Logger::class),
            $this->createMock(Collection::class),
            $this->createMock(ScopeConfigInterface::class)
        );
        $handle = tmpfile();
        $this->assertIsResource($handle);
        flock($handle, LOCK_EX);

        $property = new \ReflectionProperty(Process::class, 'lockFile');
        $property->setValue($subject, $handle);

        $subject->releaseLock();

        $this->assertFalse(is_resource($handle));
        $this->assertNull($property->getValue($subject));
    }

    private function createProcess(Generator $generator, Logger $logger): Process
    {
        $queue = $this->createCompatibleMock(
            Queue::class,
            ['getGenerator', 'setRunning', 'getFeedId']
        );
        $queue->method('getFeedId')->willReturn(17);
        $queue->method('getGenerator')->willReturn($generator);
        $queue->method('setRunning')->willReturnSelf();

        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQueue'])
            ->getMock();
        $collection->method('getQueue')->willReturn($queue);

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->with(Process::XML_PATH_ENABLED)->willReturn(true);

        return new class (
            $this->createMock(DirectoryList::class),
            $this->createMock(File::class),
            $logger,
            $collection,
            $scopeConfig
        ) extends Process {
            public bool $released = false;

            public function acquireLock()
            {
                return true;
            }

            public function releaseLock()
            {
                $this->released = true;
                return $this;
            }
        };
    }
}
