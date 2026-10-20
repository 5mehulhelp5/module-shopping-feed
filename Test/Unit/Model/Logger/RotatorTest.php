<?php
declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Logger;

use MageOS\ShoppingFeed\Model\Logger\Rotator;
use PHPUnit\Framework\TestCase;

class RotatorTest extends TestCase
{
    public function testArchivesAreCompleteAndUniqueBeforeTheLogIsTruncated(): void
    {
        $log = tempnam(sys_get_temp_dir(), 'feed-log-');
        $archives = [];
        $content = str_repeat("Feed log entry\n", 10000);
        try {
            $rotator = new Rotator();
            for ($i = 0; $i < 2; $i++) {
                file_put_contents($log, $content);
                $archives[] = $rotator->rotate($log);
                self::assertSame($content, file_get_contents('compress.zlib://' . end($archives)));
                self::assertSame('', file_get_contents($log));
            }
            self::assertNotSame($archives[0], $archives[1]);
        } finally {
            foreach ($archives as $archive) {
                unlink($archive);
            }
            unlink($log);
        }
    }

    public function testFailedArchiveLeavesTheOriginalLogUntouched(): void
    {
        $log = tempnam(sys_get_temp_dir(), 'feed-log-');
        $rotator = new class extends Rotator {
            protected function openArchive(string $path)
            {
                return false;
            }
        };
        try {
            file_put_contents($log, 'Original log content');
            try {
                $rotator->rotate($log);
                self::fail('A failed archive must reject rotation.');
            } catch (\RuntimeException $exception) {
                self::assertSame('Original log content', file_get_contents($log));
            }
        } finally {
            unlink($log);
        }
    }
}
