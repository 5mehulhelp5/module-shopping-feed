<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Test\Unit\Model\Adminhtml;

use MageOS\ShoppingFeed\Model\Adminhtml\FeedRow;
use MageOS\ShoppingFeed\Model\Feed;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class FeedRowTest extends TestCase
{
    public function testGridPayloadExcludesSecretsAndUsesPermissionCheckedPostActions(): void
    {
        $feed = $this->getMockBuilder(Feed::class)->disableOriginalConstructor()
            ->onlyMethods(['getFormattedSchedules', 'getMessages'])->getMock();
        $feed->setData(['id'=>7,'name'=>'Fixture','store_id'=>1,'type'=>'generic','status'=>0,
            'config'=>['private'=>'secret'], 'uploads'=>[['password'=>'secret']], 'messages'=>'private log']);
        $feed->method('getFormattedSchedules')->willReturn([]);
        $feed->method('getMessages')->willReturn([]);
        $url = $this->createMock(\Magento\Backend\Model\UrlInterface::class);
        $url->method('getUrl')->willReturnCallback(fn($route) => 'https://local.test/admin/' . $route);
        $key = $this->createMock(\Magento\Framework\Data\Form\FormKey::class);
        $key->method('getFormKey')->willReturn('synthetic-form-key');
        $directories = $this->createMock(\Magento\Framework\App\Filesystem\DirectoryList::class);
        $directories->method('getPath')->willReturn('/nonexistent-shopping-feed-test');
        $escaper = $this->createMock(\Magento\Framework\Escaper::class);
        foreach (['escapeHtml', 'escapeHtmlAttr', 'escapeUrl'] as $method) {
            $escaper->method($method)->willReturnCallback(fn($text) => htmlspecialchars((string) $text, ENT_QUOTES));
        }
        foreach ([true, false] as $allowed) {
            $auth = $this->createMock(\Magento\Framework\AuthorizationInterface::class);
            $auth->method('isAllowed')->willReturn($allowed);
            $formatter = new FeedRow($escaper,$url,$auth,$key,
                $this->createMock(\Magento\Store\Model\StoreManagerInterface::class),$directories);
            $row=$formatter->format($feed);
            foreach (['config','uploads','messages'] as $field) self::assertArrayNotHasKey($field,$row);
            if ($allowed) {
                self::assertStringContainsString('<form method="post"', $row['actions']);
                self::assertStringContainsString('name="form_key"', $row['actions']);
                self::assertStringNotContainsString('href="https://local.test/admin/mageos_shopping_feed/feed/generate', $row['actions']);
            } else {
                self::assertSame('', $row['actions']);
            }
        }
    }
}
