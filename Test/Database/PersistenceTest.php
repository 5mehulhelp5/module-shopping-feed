<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Test\Database;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Feed\Upload;
use MageOS\ShoppingFeed\Model\Generator\Batch;
use MageOS\ShoppingFeed\Model\Generator\Queue;
use MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue\Collection;
use PHPUnit\Framework\TestCase;

/** Real framework persistence against session-local temporary tables and a synthetic encryption key. */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class PersistenceTest extends TestCase
{
    private Mysql $db;
    private ObjectManager $helper;
    private Context $resourceContext;
    private \Magento\Framework\Encryption\Encryptor $encryptor;

    protected function setUp(): void
    {
        if (!getenv('SHOPPING_FEED_TEST_DB_PORT')) {
            $this->markTestSkipped('Set SHOPPING_FEED_TEST_DB_PORT to a disposable localhost MariaDB instance.');
        }
        $this->helper = new ObjectManager($this);
        $renderers = [];
        $quote = new \Magento\Framework\DB\Platform\Quote();
        foreach (['Distinct', 'Columns', 'Union', 'From', 'Where', 'Group', 'Having', 'Order', 'Limit', 'ForUpdate'] as $i => $name) {
            $class = 'Magento\\Framework\\DB\\Select\\' . $name . 'Renderer';
            $renderers[] = [
                'sort' => $i, 'part' => ['Limit' => 'limitcount', 'ForUpdate' => 'forupdate'][$name] ?? strtolower($name),
                'renderer' => new $class($quote),
            ];
        }
        $selectFactory = new \Magento\Framework\DB\SelectFactory(new \Magento\Framework\DB\Select\SelectRenderer($renderers));
        $this->db = $this->helper->getObject(Mysql::class, [
            'string' => new \Magento\Framework\Stdlib\StringUtils(),
            'dateTime' => new \Magento\Framework\Stdlib\DateTime(),
            'selectFactory' => $selectFactory,
            'serializer' => new \Magento\Framework\Serialize\Serializer\Json(),
            'config' => ['host' => '127.0.0.1:' . (int)getenv('SHOPPING_FEED_TEST_DB_PORT'),
                'dbname' => 'shopping_feed_test', 'username' => 'root', 'password' => '', 'charset' => 'utf8mb4'],
        ]);
        $resources = $this->createMock(ResourceConnection::class);
        $resources->method('getConnection')->willReturn($this->db);
        $resources->method('getTableName')->willReturnArgument(0);
        $this->resourceContext = new Context($resources,
            new \Magento\Framework\Model\ResourceModel\Db\TransactionManager(),
            $this->createMock(\Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class)
        );
        $deployment = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $deployment->method('get')->with('crypt/key')->willReturn(str_repeat('a', 32));
        $this->encryptor = new \Magento\Framework\Encryption\Encryptor(
            new \Magento\Framework\Math\Random(), $deployment,
            $this->createMock(\Magento\Framework\Encryption\KeyValidator::class)
        );
        $schema = simplexml_load_file(dirname(__DIR__, 2) . '/etc/db_schema.xml');
        $column = $schema->xpath('//table[@name="mageos_shopping_feed_feed_upload"]/column[@name="password"]')[0];
        $type = (string)$column->attributes('xsi', true)->type;
        $type .= isset($column['length']) ? '(' . (int)$column['length'] . ')' : '';
        $this->db->query('CREATE TEMPORARY TABLE mageos_shopping_feed_feed_upload (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, feed_id INT, host VARCHAR(255), username VARCHAR(255),
            password ' . $type . ', port INT, mode VARCHAR(255), gzip INT, path VARCHAR(255))');
        $this->db->query('CREATE TEMPORARY TABLE mageos_shopping_feed_feed (id INT PRIMARY KEY)');
        $this->db->query('CREATE TEMPORARY TABLE mageos_shopping_feed_feed_queue (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, feed_id INT, schedule_id INT NULL,
            is_read INT DEFAULT 0, message TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');
        $this->db->insert('mageos_shopping_feed_feed', ['id' => 1]);
    }

    protected function tearDown(): void
    {
        if (isset($this->db)) {
            $this->db->closeConnection(); // All three temporary tables disappear with the connection.
        }
    }

    private function upload(): Upload
    {
        return $this->helper->getObject(Upload::class, [
            'encryptor' => $this->encryptor,
            'resource' => new \MageOS\ShoppingFeed\Model\ResourceModel\Feed\Upload($this->resourceContext),
        ]);
    }

    public function testMaskedAdminSavePreservesRawCiphertext(): void
    {
        $password = 'synthetic password';
        $upload = $this->upload()->setData(['feed_id' => 1, 'password' => $password]);
        $upload->save();
        $id = $upload->getId();
        $stored = $this->db->fetchOne('SELECT password FROM mageos_shopping_feed_feed_upload WHERE id = ?', $id);
        $this->assertNotSame($password, $stored);
        $factory = $this->createMock(\MageOS\ShoppingFeed\Model\Feed\UploadFactory::class);
        $factory->method('create')->willReturnCallback(fn() => $this->upload());
        $feed = (new \ReflectionClass(Feed::class))->newInstanceWithoutConstructor();
        $feed->setId(1);
        (new \ReflectionProperty(Feed::class, 'uploadFactory'))->setValue($feed, $factory);
        $row = ['id' => $id, 'delete' => 0, 'host' => 'changed.example', 'port' => 22, 'mode' => 'sftp',
            'username' => 'fixture', 'password' => Upload::OBSCURED_VALUE, 'gzip' => 0, 'path' => ''];
        $feed->setData('uploads', [$row])->saveUploads();
        $raw = $this->db->fetchRow('SELECT * FROM mageos_shopping_feed_feed_upload WHERE id = ?', $id);
        $this->assertSame('changed.example', $raw['host']);
        $this->assertSame($stored, $raw['password']);
        $this->assertSame($password, $this->upload()->load($id)->getPassword());

    }

    public function testRepeatedSaveDoesNotDoubleEncryptThePassword(): void
    {
        $password = 'synthetic password';
        $upload = $this->upload()->setData(['feed_id' => 1, 'password' => $password]);
        $upload->save();
        $upload->setData('host', 'changed.example')->save();
        $raw = $this->db->fetchOne('SELECT password FROM mageos_shopping_feed_feed_upload WHERE id = ?', $upload->getId());
        $this->assertSame($password, $this->encryptor->decrypt($raw));
        $this->assertSame($password, $this->upload()->load($upload->getId())->getPassword());
    }

    public function testLongPasswordExceedsTheOldColumnLimitAndRoundTrips(): void
    {
        $password = str_repeat('long synthetic passphrase ', 20) . 'end';
        $upload = $this->upload()->setData(['feed_id' => 1, 'password' => $password]);
        $upload->save();
        $id = $upload->getId();
        $raw = $this->db->fetchOne('SELECT password FROM mageos_shopping_feed_feed_upload WHERE id = ?', $id);
        $this->assertGreaterThan(255, strlen($raw));
        $this->assertSame($password, $this->encryptor->decrypt($raw));
        $this->assertSame($password, $this->upload()->load($id)->getPassword());
    }

    public function testTodaysInterruptedQueueIsImmediatelyEligibleAndResetsItsBatch(): void
    {
        $resource = new \MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue($this->resourceContext);
        $makeQueue = function () use ($resource): Queue {
            $queue = $this->helper->getObject(Queue::class, [
                'resource' => $resource, 'serializer' => new \Magento\Framework\Serialize\Serializer\Json(),
            ]);
            $queue->setBatch(new Batch());
            return $queue;
        };
        $entityFactory = $this->createMock(\Magento\Framework\Data\Collection\EntityFactoryInterface::class);
        $entityFactory->method('create')->willReturnCallback($makeQueue);
        $date = $this->createMock(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $date->method('date')->willReturn(date('Y-m-d H:i:s'));
        $lookup = $this->helper->getObject(Collection::class, [
            'entityFactory' => $entityFactory, 'date' => $date,
            'connection' => $this->db, 'resource' => $resource,
            'fetchStrategy' => new \Magento\Framework\Data\Collection\Db\FetchStrategy\Query(new \Psr\Log\NullLogger()),
        ]);
        $this->db->insert('mageos_shopping_feed_feed_queue', [
            'feed_id' => 1, 'is_read' => 1, 'message' => json_encode(['enabled' => true, 'offset' => 500, 'limit' => 100]),
        ]);
        $queue = $lookup->getQueue(1);
        $this->assertNotNull($queue->getId());
        $this->assertSame(500, $queue->getBatch()->getOffset());
        $queue->setRunning();
        $saved = $makeQueue()->load($queue->getId());
        $this->assertSame(0, $saved->getBatch()->getOffset());
        $this->assertSame(100, $saved->getBatch()->getLimit());
        $this->assertSame((int)$queue->getId(), (int)$lookup->getQueue()->getId());
    }
}
