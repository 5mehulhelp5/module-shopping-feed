<?php
declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Uploader\Connection;

use Magento\Framework\Exception\LocalizedException;
use phpseclib3\Net\SFTP as Client;

/** SFTP transport that authenticates the server before sending credentials. */
class Sftp extends \Magento\Framework\Filesystem\Io\Sftp
{
    /**
     * Open a connection only when its host key matches the destination's pinned fingerprint.
     *
     * @param array $args Connection arguments keyed by name.
     * @phpstan-param array<string, mixed> $args
     * @return bool
     * @throws LocalizedException
     */
    public function open(array $args = [])
    {
        $fingerprint = trim((string)($args['host_key_fingerprint'] ?? ''));
        if (!preg_match('#^SHA256:[A-Za-z0-9+/]{43}$#D', $fingerprint)) {
            throw new LocalizedException(__('Enter a trusted SHA256 SFTP host key fingerprint.'));
        }

        $client = $this->createClient(
            (string)$args['host'],
            (int)($args['port'] ?? self::SSH2_PORT),
            (int)($args['timeout'] ?? self::REMOTE_TIMEOUT)
        );
        try {
            $key = $client->getServerPublicHostKey();
            $parts = is_string($key) ? explode(' ', $key, 3) : [];
            // phpcs:ignore Magento2.Functions.DiscouragedFunction -- SSH fingerprints hash the decoded public key blob
            $blob = isset($parts[1]) ? base64_decode($parts[1], true) : false;
            $actual = $blob === false ? '' : 'SHA256:' . rtrim(base64_encode(hash('sha256', $blob, true)), '=');
            if (!hash_equals($fingerprint, $actual)) {
                throw new LocalizedException(
                    __('SFTP host key verification failed. Confirm the key with your provider.')
                );
            }
            if (!$client->login($args['username'], $args['password'])) {
                throw new LocalizedException(__('SFTP authentication failed. Check the destination credentials.'));
            }
            $this->_connection = $client;
            return true;
        } catch (\Throwable $exception) {
            $client->disconnect();
            throw $exception;
        }
    }

    /**
     * Create the unauthenticated SSH client.
     *
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @return Client
     */
    protected function createClient(string $host, int $port, int $timeout): Client
    {
        return new Client($host, $port, $timeout);
    }
}
