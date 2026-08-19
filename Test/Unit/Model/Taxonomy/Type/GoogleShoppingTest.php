<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Taxonomy\Type;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Serialize\SerializerInterface;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Taxonomy\Type\GoogleShopping;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class GoogleShoppingTest extends TestCase
{
    public function testTaxonomyUrlUsesHttps(): void
    {
        $subject = $this->createSubject('en-US');

        $this->assertSame(
            'https://www.google.com/basepages/producttype/taxonomy.en-US.txt',
            $subject->getTaxonomyUrl()
        );
    }

    public function testInvalidLocaleCannotAlterTaxonomyRequestPath(): void
    {
        $subject = $this->createSubject('../attacker');

        $this->assertSame(
            'https://www.google.com/basepages/producttype/taxonomy.en-US.txt',
            $subject->getTaxonomyUrl()
        );
    }

    public function testSuccessfulResponseIsParsedAndConnectionIsClosed(): void
    {
        $curl = $this->createMock(Curl::class);
        $curl->expects($this->once())
            ->method('setOptions')
            ->with(['timeout' => 15, 'header' => false])
            ->willReturnSelf();
        $curl->expects($this->once())
            ->method('write')
            ->with('GET', 'https://www.google.com/basepages/producttype/taxonomy.en-US.txt', '1.0');
        $curl->expects($this->once())
            ->method('read')
            ->willReturn("# Google Product Taxonomy\r\n1 - Animals & Pet Supplies\r\n\r\n2 - Apparel\r\n");
        $curl->expects($this->once())
            ->method('getInfo')
            ->with(CURLINFO_HTTP_CODE)
            ->willReturn(200);
        $curl->expects($this->once())->method('close');

        $this->assertSame(
            ['1 - Animals & Pet Supplies', '2 - Apparel'],
            $this->createSubject('en-US', $curl)->getTaxonomyData()
        );
    }

    public function testNonSuccessfulResponseIsRejectedAndConnectionIsClosed(): void
    {
        $curl = $this->createMock(Curl::class);
        $curl->method('setOptions')->willReturnSelf();
        $curl->method('read')->willReturn('<html>Service unavailable</html>');
        $curl->method('getInfo')->with(CURLINFO_HTTP_CODE)->willReturn(503);
        $curl->expects($this->once())->method('close');

        $this->assertFalse($this->createSubject('en-US', $curl)->getTaxonomyData());
    }

    public function testConnectionIsClosedWhenReadThrows(): void
    {
        $curl = $this->createMock(Curl::class);
        $curl->method('setOptions')->willReturnSelf();
        $curl->method('read')->willThrowException(new \RuntimeException('network failed'));
        $curl->expects($this->once())->method('close');

        $this->expectException(\RuntimeException::class);
        $this->createSubject('en-US', $curl)->getTaxonomyData();
    }

    private function createSubject(string $locale, ?Curl $curl = null): GoogleShopping
    {
        $feed = $this->getMockBuilder(Feed::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfig'])
            ->getMock();
        $feed->method('getConfig')
            ->with('categories_locale')
            ->willReturn($locale);

        $curlFactory = $this->createMock(CurlFactory::class);
        if ($curl !== null) {
            $curlFactory->method('create')->willReturn($curl);
        }

        return new GoogleShopping(
            $feed,
            $this->createMock(CacheInterface::class),
            $curlFactory,
            $this->createMock(SerializerInterface::class)
        );
    }
}
