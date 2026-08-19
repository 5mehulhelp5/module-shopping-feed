<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Setup;

use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class SchemaContractTest extends TestCase
{
    private DOMXPath $xpath;

    protected function setUp(): void
    {
        parent::setUp();

        $document = new DOMDocument();
        $document->load(dirname(__DIR__, 3) . '/etc/db_schema.xml', LIBXML_NONET);
        $this->xpath = new DOMXPath($document);
    }

    public function testManualQueueRowsDoNotRequireScheduleId(): void
    {
        $column = $this->getQueueColumn('schedule_id');

        $this->assertSame('true', $column->getAttribute('nullable'));
    }

    public function testQueueReadStateDefaultsToUnread(): void
    {
        $column = $this->getQueueColumn('is_read');

        $this->assertSame('false', $column->getAttribute('nullable'));
        $this->assertSame('0', $column->getAttribute('default'));
    }

    private function getQueueColumn(string $name): DOMElement
    {
        $nodes = $this->xpath->query(
            sprintf('//table[@name="mageos_shopping_feed_feed_queue"]/column[@name="%s"]', $name)
        );

        $this->assertNotFalse($nodes);
        $this->assertSame(1, $nodes->length);

        /** @var DOMElement $column */
        $column = $nodes->item(0);
        return $column;
    }
}
