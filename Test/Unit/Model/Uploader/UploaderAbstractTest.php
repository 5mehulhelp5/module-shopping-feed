<?php

namespace MageOS\ShoppingFeed\Test\Unit\Model\Uploader;

use MageOS\ShoppingFeed\Model\Uploader\UploaderAbstract;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class UploaderAbstractTest extends TestCase
{
    public function testReplacesFeedUploadWhenCachedUploaderIsReused(): void
    {
        $first = $this->createMock('MageOS\ShoppingFeed\Model\Feed\Upload');
        $second = $this->createMock('MageOS\ShoppingFeed\Model\Feed\Upload');
        $uploader = new class ($first) extends UploaderAbstract {
            public function getConnectionConfiguration($config)
            {
                return $config;
            }

            public function getFeedUpload()
            {
                return $this->feedUpload;
            }
        };

        $this->assertSame($uploader, $uploader->setFeedUpload($second));
        $this->assertSame($second, $uploader->getFeedUpload());
    }
    public function testFailedDirectoryChangePreventsUploadAndValidation(): void
    {
        foreach (['upload', 'checkConnection'] as $operation) {
            $config = $this->createMock(\MageOS\ShoppingFeed\Model\Feed\Upload::class);
            $config->method('getData')->willReturn(['host' => 'example.invalid', 'port' => 22, 'username' => 'test', 'password' => 'dummy', 'path' => '/missing']);
            $connection = $this->createMock(\Magento\Framework\Filesystem\Io\Sftp::class);
            $connection->method('open')->willReturn(true);
            $connection->method('cd')->with('/missing')->willReturn(false);
            $connection->expects($this->never())->method('write');
            $subject = new \MageOS\ShoppingFeed\Model\Uploader\Mode\Sftp($config, $connection);
            try {
                $operation === 'upload' ? $subject->upload('/tmp/feed.txt') : $subject->checkConnection();
                $this->fail('A failed directory change must reject ' . $operation);
            } catch (\Magento\Framework\Exception\LocalizedException $exception) {
                $this->assertStringContainsString('/missing', $exception->getMessage());
            }
        }
    }

    public function testSuccessfulDirectoryChangeAllowsUpload(): void
    {
        $config = $this->createMock(\MageOS\ShoppingFeed\Model\Feed\Upload::class);
        $config->method('getData')->willReturn(['host' => 'example.invalid', 'port' => 22, 'username' => 'test', 'password' => 'dummy', 'path' => '/feeds']);
        $connection = $this->createMock(\Magento\Framework\Filesystem\Io\Sftp::class);
        $connection->method('open')->willReturn(true);
        $connection->method('cd')->with('/feeds')->willReturn(true);
        $connection->expects($this->once())->method('write')->with('feed.txt', '/tmp/feed.txt')->willReturn(true);
        $subject = new \MageOS\ShoppingFeed\Model\Uploader\Mode\Sftp($config, $connection);
        $this->assertTrue($subject->upload('/tmp/feed.txt'));
    }

}
