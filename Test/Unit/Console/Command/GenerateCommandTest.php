<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use MageOS\ShoppingFeed\Console\Command\GenerateCommand;
use MageOS\ShoppingFeed\Cron\Process;
use MageOS\ShoppingFeed\Cron\ProcessFactory;
use MageOS\ShoppingFeed\Model\Generator\QueueFactory;
use MageOS\ShoppingFeed\Model\Logger;
use MageOS\ShoppingFeed\Model\Logger\Handler\Console;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class GenerateCommandTest extends TestCase
{
    public function testQueueGenerationReturnsFailureWhenProcessingFails(): void
    {
        $process = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setDetached', 'execute'])
            ->getMock();
        $process->method('setDetached')->willReturnSelf();
        $process->method('execute')->willReturn(false);

        $processFactory = $this->createMock(ProcessFactory::class);
        $processFactory->method('create')->willReturn($process);

        $command = new GenerateCommand(
            $this->createMock(State::class),
            $processFactory,
            $this->createMock(QueueFactory::class),
            $this->createMock(Logger::class),
            $this->createMock(Console::class)
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([]);

        $this->assertSame(Cli::RETURN_FAILURE, $exitCode);
        $this->assertStringContainsString('Generation failed.', $tester->getDisplay());
        $this->assertStringNotContainsString('Done!', $tester->getDisplay());
    }
}
