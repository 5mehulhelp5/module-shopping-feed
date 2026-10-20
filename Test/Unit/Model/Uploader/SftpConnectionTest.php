<?php
declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Uploader;

use MageOS\ShoppingFeed\Model\Uploader\Connection\Sftp;
use phpseclib3\Net\SFTP as Client;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class SftpConnectionTest extends TestCase
{
    public function testMatchingKeyIsVerifiedBeforeLogin(): void
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()
            ->onlyMethods(['getServerPublicHostKey', 'login', 'disconnect', '__destruct'])->getMock();
        $verified = false;
        $client->method('getServerPublicHostKey')->willReturnCallback(static function () use (&$verified) {
            $verified = true;
            return 'ssh-ed25519 ' . base64_encode('test public key');
        });
        $client->expects(self::once())->method('login')->willReturnCallback(
            static function ($username, $password) use (&$verified) {
                self::assertTrue($verified);
                self::assertSame('merchant', $username);
                self::assertSame('test credential', $password);
                return true;
            }
        );
        $this->connection($client)->open($this->config());
    }

    public function testChangedKeyNeverReceivesCredentials(): void
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()
            ->onlyMethods(['getServerPublicHostKey', 'login', 'disconnect', '__destruct'])->getMock();
        $client->method('getServerPublicHostKey')->willReturn('ssh-ed25519 ' . base64_encode('changed key'));
        $client->expects(self::never())->method('login');
        $client->expects(self::atLeastOnce())->method('disconnect');
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->connection($client)->open($this->config());
    }

    public function testMissingFingerprintFailsBeforeConnecting(): void
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()
            ->onlyMethods(['getServerPublicHostKey', 'login', 'disconnect', '__destruct'])->getMock();
        $client->expects(self::never())->method('getServerPublicHostKey');
        $client->expects(self::never())->method('login');
        $config = $this->config();
        unset($config['host_key_fingerprint']);
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->connection($client)->open($config);
    }

    /** @return array<string, mixed> */
    private function config(): array
    {
        return ['host' => 'example.invalid', 'port' => 22, 'username' => 'merchant',
            'password' => 'test credential', 'host_key_fingerprint' =>
                'SHA256:' . rtrim(base64_encode(hash('sha256', 'test public key', true)), '=')];
    }

    private function connection(Client $client): Sftp
    {
        return new class($client) extends Sftp {
            public function __construct(private Client $client)
            {
            }

            protected function createClient(string $host, int $port, int $timeout): Client
            {
                return $this->client;
            }
        };
    }
}
