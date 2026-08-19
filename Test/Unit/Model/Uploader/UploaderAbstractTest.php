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
}
