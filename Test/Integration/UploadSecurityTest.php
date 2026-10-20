<?php
declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Integration;

use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Feed\Copier;
use MageOS\ShoppingFeed\Model\Feed\Upload;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class UploadSecurityTest extends TestCase
{
    /**
     * @magentoDbIsolation disabled
     */
    public function testFailedDestinationCloneRollsBackTheNewFeed(): void
    {
        $om = Bootstrap::getObjectManager();
        $feed = $om->create(Feed::class)->setType('generic')->setStoreId(1)->setStatus(0)->setUseMicrodata(0)
            ->setName('Atomic upload clone')->setSchedules([])->setUploads([])->save();
        $om->create(Upload::class)->setData([
            'feed_id' => $feed->getId(), 'mode' => 'sftp', 'host' => 'example.invalid', 'port' => 22,
            'username' => 'test', 'password' => 'Synthetic fixture password', 'path' => '', 'gzip' => 0,
        ])->save();
        $feed = $om->create(Feed::class)->load($feed->getId());
        $db = $om->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();
        $select = $db->select()->from($db->getTableName('mageos_shopping_feed_feed'), 'COUNT(*)');
        $before = (int)$db->fetchOne($select);
        $resource = $this->getMockBuilder(\MageOS\ShoppingFeed\Model\ResourceModel\Feed\Upload::class)
            ->setConstructorArgs([$om->get(\Magento\Framework\Model\ResourceModel\Db\Context::class)])
            ->onlyMethods(['save'])->getMock();
        $resource->expects(self::once())->method('save')->willThrowException(new \RuntimeException('Fixture failure'));
        try {
            try {
                $om->create(Copier::class, ['uploadResource' => $resource])->copy($feed);
                self::fail('A failed destination copy must reject the clone.');
            } catch (\RuntimeException $expected) {
                self::assertSame('Fixture failure', $expected->getMessage());
            }
            self::assertSame($before, (int)$db->fetchOne($select));
        } finally {
            $feed->delete();
        }
    }

    public function testSavedLoadedAndClonedDestinationsKeepCiphertextInModelData(): void
    {
        $om = Bootstrap::getObjectManager();
        $password = 'Synthetic upload integration credential';
        $fingerprint = 'SHA256:' . rtrim(base64_encode(hash('sha256', 'test host key', true)), '=');
        $feed = $om->create(Feed::class)->setType('generic')->setStoreId(1)->setStatus(0)->setUseMicrodata(0)
            ->setName('Upload security integration')->setSchedules([])->setUploads([])->save();
        $upload = $om->create(Upload::class)->setData([
            'feed_id' => $feed->getId(), 'mode' => 'sftp', 'host' => 'example.invalid', 'port' => 22,
            'username' => 'test', 'password' => $password, 'path' => '/feeds', 'gzip' => 0,
            'host_key_fingerprint' => $fingerprint,
        ])->save();
        $ciphertext = $upload->getData('password');
        self::assertNotSame($password, $ciphertext);
        self::assertSame($password, $upload->getDecryptedPassword());
        $loaded = $om->create(Upload::class)->load($upload->getId());
        self::assertSame($ciphertext, $loaded->getData('password'));
        self::assertSame($ciphertext, $loaded->getOrigData('password'));
        $loaded->setPassword(Upload::OBSCURED_VALUE)->save();
        self::assertSame($ciphertext, $loaded->getData('password'));

        $feed = $om->create(Feed::class)->load($feed->getId());
        self::assertSame($ciphertext, $feed->getUploads()[0]['password']);
        $copy = $om->create(Copier::class)->copy($feed);
        $copied = $copy->getUploadCollection()->getFirstItem();
        self::assertNotSame((int)$upload->getId(), (int)$copied->getId());
        self::assertSame($ciphertext, $copied->getData('password'));
        self::assertSame($password, $copied->getDecryptedPassword());
        self::assertSame($fingerprint, $copied->getData('host_key_fingerprint'));
        self::assertSame($ciphertext, $copy->getUploads()[0]['password']);
        self::assertSame($fingerprint, $om->create(Feed::class)->load($copy->getId())->getUploads()[0]
            ['host_key_fingerprint']);
    }
}
