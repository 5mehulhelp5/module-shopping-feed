<?php

namespace MageOS\ShoppingFeed\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\FeedFactory;
use MageOS\ShoppingFeed\Ui\Component\Listing\Column\File;
use MageOS\ShoppingFeed\Ui\Component\Listing\Column\File\Plugin;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class FileRenderingTest extends TestCase
{
    private string $root;
    private string $relative = 'pub/media/mageos-shopping-feed/pub/feed.txt';

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/feed-grid-' . bin2hex(random_bytes(6));
        mkdir($this->root . '/pub/media/mageos-shopping-feed/pub', 0700, true);
        file_put_contents($this->root . '/' . $this->relative, 'feed');
        file_put_contents($this->root . '/private.txt', 'private');
    }

    protected function tearDown(): void
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
            $this->root, \FilesystemIterator::SKIP_DOTS
        ), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->root);
    }

    public function testFileLinkUsesMediaBaseAndPreservesNestedPubWithoutDocumentRoot(): void
    {
        $messages = [
            'file' => $this->relative, 'store_id' => 2, 'added' => 25, 'exported' => 25,
            'skipped' => 0, 'date' => '<img src=x onerror=alert(1)>',
        ];
        $column = (new ObjectManager($this))->getObject(File::class, $this->dependencies($messages));
        $column->setName('file');
        $originalRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;
        unset($_SERVER['DOCUMENT_ROOT']);
        try {
            $result = $column->prepareDataSource(['data' => ['items' => [['id' => 1, 'store_id' => 2]]]]);
        } finally {
            if ($originalRoot !== null) {
                $_SERVER['DOCUMENT_ROOT'] = $originalRoot;
            }
        }
        $html = $result['data']['items'][0]['file'];
        self::assertStringContainsString('href="https://cdn.example.test/media/mageos-shopping-feed/pub/feed.txt"', $html);
        self::assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
        self::assertStringNotContainsString('<img', $html);
        self::assertStringContainsString('rel="noopener"', $html);
    }

    public function testPromotionLinkNeverExposesFilesOutsideMedia(): void
    {
        $messages = ['promotion_file' => $this->root . '/private.txt', 'promotion_added' => 1];
        $plugin = (new ObjectManager($this))->getObject(Plugin::class, $this->dependencies($messages));
        $input = ['data' => ['items' => [['id' => 1, 'store_id' => 2, 'file' => 'Feed file not ready.']]]];
        self::assertSame($input, $plugin->afterPrepareDataSource(new \Magento\Framework\DataObject(['name' => 'file']), $input));
    }

    public function testPromotionLinkUsesMediaBaseAndEscapesCounts(): void
    {
        $messages = ['promotion_file' => $this->root . '/' . $this->relative,
            'promotion_added' => '<img src=x onerror=alert(1)>'];
        $plugin = (new ObjectManager($this))->getObject(Plugin::class, $this->dependencies($messages));
        $result = $plugin->afterPrepareDataSource(new \Magento\Framework\DataObject(['name' => 'file']),
            ['data' => ['items' => [['id' => 1, 'store_id' => 2, 'file' => 'Feed']]]]);
        $html = $result['data']['items'][0]['file'];
        self::assertStringContainsString('href="https://cdn.example.test/media/mageos-shopping-feed/pub/feed.txt"', $html);
        self::assertStringNotContainsString('<img', $html);
        self::assertStringContainsString('rel="noopener"', $html);
    }

    private function dependencies(array $messages): array
    {
        $feed = $this->getMockBuilder(Feed::class)->disableOriginalConstructor()->onlyMethods(['getMessages'])->getMock();
        $feed->method('getMessages')->willReturn($messages);
        $factory = $this->createMock(FeedFactory::class);
        $factory->method('create')->willReturn($feed);
        $store = $this->createMock(Store::class);
        $store->method('getBaseUrl')->willReturnCallback(static fn ($type = null) =>
            $type === UrlInterface::URL_TYPE_MEDIA ? 'https://cdn.example.test/media/' : 'https://store.example.test/');
        $stores = $this->createMock(StoreManagerInterface::class);
        $stores->method('getStore')->willReturn($store);
        $directories = $this->createMock(DirectoryList::class);
        $directories->method('getRoot')->willReturn($this->root);
        $directories->method('getPath')->with(DirectoryList::MEDIA)->willReturn($this->root . '/pub/media');
        $escaper = new Escaper();
        (new \ReflectionProperty(Escaper::class, 'translateInline'))->setValue(
            $escaper, $this->createMock(\Magento\Framework\Translate\InlineInterface::class)
        );
        return ['feedFactory' => $factory, 'storeManager' => $stores,
            'directoryList' => $directories, 'escaper' => $escaper];
    }
}
