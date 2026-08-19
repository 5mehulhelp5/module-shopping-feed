<?php

namespace MageOS\ShoppingFeed\Test\Unit\Model\Uploader;

use MageOS\ShoppingFeed\Model\Uploader\GzipCompressor;
use Magento\Framework\Filesystem\Driver\File;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class GzipCompressorTest extends TestCase
{
    /**
     * @var string
     */
    private $sourcePath;

    protected function setUp(): void
    {
        $this->sourcePath = TESTS_TEMP_DIR . '/shopping-feed-gzip-' . uniqid('', true) . '.txt';
    }

    protected function tearDown(): void
    {
        foreach ([$this->sourcePath, $this->sourcePath . '.gz', $this->sourcePath . '.gz.tmp'] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testCompressesLargeFeedInAValidGzipStream(): void
    {
        $contents = str_repeat("sku\tprice\nABC-1\t10.00 USD\n", 100000);
        file_put_contents($this->sourcePath, $contents);
        $compressor = new GzipCompressor(new File());

        $gzipPath = $compressor->compress($this->sourcePath);

        $this->assertSame($this->sourcePath . '.gz', $gzipPath);
        $this->assertFileExists($gzipPath);
        $this->assertSame($contents, gzdecode((string) file_get_contents($gzipPath)));

        $compressor->remove($gzipPath);
        $this->assertFileDoesNotExist($gzipPath);
    }
}
