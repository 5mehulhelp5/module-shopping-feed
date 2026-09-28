<?php
declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Feed;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use MageOS\ShoppingFeed\Model\Feed\Upload;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class UploadTest extends TestCase
{
    private function createUpload(): Upload
    {
        $encryptor = $this->createMock(EncryptorInterface::class);
        $encryptor->method('encrypt')->willReturnCallback(static fn($value) => 'encrypted:' . base64_encode($value));
        $encryptor->method('decrypt')->willReturnCallback(
            static fn($value) => str_starts_with((string)$value, 'encrypted:') ? base64_decode(substr($value, 10)) : ''
        );
        return (new ObjectManager($this))->getObject(Upload::class, ['encryptor' => $encryptor]);
    }

    public function testMaskedSavePreservesCiphertextAfterResourceLoadSetsOriginalData(): void
    {
        $upload = $this->createUpload();
        $ciphertext = 'encrypted:' . base64_encode('synthetic password');
        $upload->setData(['id' => 1, 'password' => $ciphertext]);
        $upload->afterLoad();
        // AbstractDb::load records original data after the model afterLoad hook.
        $upload->setOrigData();
        $this->assertSame('synthetic password', $upload->getPassword());
        $upload->setData('host', 'new.example')->setPassword(Upload::OBSCURED_VALUE)->beforeSave();
        $this->assertSame($ciphertext, $upload->getData('password'));
    }

    public function testNewAndRotatedPasswordsSurviveRepeatedSavesAndRemainUsableForUpload(): void
    {
        $upload = $this->createUpload();
        foreach (['first password', str_repeat('long passphrase ', 30)] as $password) {
            $upload->setPassword($password);
            for ($save = 0; $save < 2; $save++) {
                $upload->beforeSave();
                $this->assertSame('encrypted:' . base64_encode($password), $upload->getData('password'));
                $upload->afterSave();
                $this->assertSame($password, $upload->getData('password'));
            }
        }
    }

    public function testNewUploadCannotRetainANonexistentPassword(): void
    {
        $upload = $this->createUpload();
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $upload->setPassword(Upload::OBSCURED_VALUE)->beforeSave();
    }

    public function testUnreadableStoredPasswordRequiresReplacement(): void
    {
        $upload = $this->createUpload();
        $upload->setData(['id' => 1, 'password' => 'legacy plaintext'])->afterLoad()->setOrigData();
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $upload->setPassword(Upload::OBSCURED_VALUE)->beforeSave();
    }
}
