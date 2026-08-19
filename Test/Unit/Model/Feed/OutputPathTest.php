<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Feed;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverPool;
use MageOS\ShoppingFeed\Model\Feed\OutputPath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class OutputPathTest extends TestCase
{
    private File $driver;

    private string $root;

    private OutputPath $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = TESTS_TEMP_DIR . '/output-path-' . bin2hex(random_bytes(8));
        $this->driver = new File();
        $this->driver->createDirectory($this->root);

        $driverPool = new DriverPool([DriverPool::FILE => $this->driver]);
        $directoryList = new DirectoryList($this->root);
        $filesystem = new Filesystem(
            $directoryList,
            new ReadFactory($driverPool),
            new WriteFactory($driverPool)
        );

        $this->subject = new OutputPath($filesystem, $directoryList);
    }

    protected function tearDown(): void
    {
        if ($this->driver->isExists($this->root)) {
            $this->driver->deleteDirectory($this->root);
        }

        parent::tearDown();
    }

    public function testResolvesFeedFileInsideDedicatedMediaDirectory(): void
    {
        $path = $this->subject->resolveFeedFile(
            'pub/media/mageos-shopping-feed',
            'products_%s.txt',
            42
        );

        $this->assertSame(
            $this->root . '/pub/media/mageos-shopping-feed/products_42.txt',
            $path
        );
        $this->assertTrue($this->driver->isDirectory($this->root . '/pub/media/mageos-shopping-feed'));
    }

    public function testReturnsRootRelativeFeedPathForStoredStatusMessages(): void
    {
        $path = $this->subject->resolveFeedFile(
            'pub/media/mageos-shopping-feed',
            'products_%s.csv',
            42,
            false
        );

        $this->assertSame('pub/media/mageos-shopping-feed/products_42.csv', $path);
    }

    public function testResolvesLogFileInsideMagentoLogDirectory(): void
    {
        $absolutePath = $this->subject->resolveLogFile('feed_%s.log', 42);
        $relativePath = $this->subject->resolveLogFile('feed_%s.log', 42, false);

        $this->assertSame($this->root . '/var/log/feed_42.log', $absolutePath);
        $this->assertSame('var/log/feed_42.log', $relativePath);
    }

    /**
     * @dataProvider unsafePathProvider
     */
    #[DataProvider('unsafePathProvider')]
    public function testRejectsUnsafeOutputConfiguration(string $directory, string $filename): void
    {
        $this->expectException(LocalizedException::class);

        $this->subject->resolveFeedFile($directory, $filename, 42);
    }

    public static function unsafePathProvider(): array
    {
        return [
            'parent directory traversal' => ['../var', 'feed_%s.txt'],
            'lookalike media directory' => ['pub/media/mageos-shopping-feed-backup', 'feed_%s.txt'],
            'absolute directory' => ['/tmp/mageos-shopping-feed', 'feed_%s.txt'],
            'filename traversal' => ['pub/media/mageos-shopping-feed', '../feed_%s.txt'],
            'nested filename' => ['pub/media/mageos-shopping-feed', 'nested/feed_%s.txt'],
            'php extension' => ['pub/media/mageos-shopping-feed', 'feed_%s.php'],
            'extra format token' => ['pub/media/mageos-shopping-feed', 'feed_%s_%s.txt'],
        ];
    }

    public function testRejectsSymlinkedOutputDirectoryThatEscapesMedia(): void
    {
        $mediaPath = $this->root . '/pub/media';
        $outsidePath = $this->root . '/outside';
        $this->driver->createDirectory($mediaPath);
        $this->driver->createDirectory($outsidePath);
        symlink($outsidePath, $mediaPath . '/mageos-shopping-feed');

        $this->expectException(LocalizedException::class);

        $this->subject->resolveFeedFile(
            'pub/media/mageos-shopping-feed',
            'products_%s.txt',
            42
        );
    }
}
