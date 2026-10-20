<?php
declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Uploader;

use MageOS\ShoppingFeed\Model\Feed\Upload;
use MageOS\ShoppingFeed\Model\Uploader\UploaderFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class UploaderFactoryTest extends TestCase
{
    public function testUnknownModeCannotSilentlyDowngradeToFtp(): void
    {
        $upload = (new \ReflectionClass(Upload::class))->newInstanceWithoutConstructor();
        $upload->setData('mode', 'sftpp');
        $objects = $this->createMock(ObjectManagerInterface::class);
        $objects->expects(self::never())->method('create');
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        (new UploaderFactory($objects))->create($upload);
    }
}
