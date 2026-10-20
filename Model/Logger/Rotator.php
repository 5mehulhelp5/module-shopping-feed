<?php
declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Logger;

// phpcs:disable Magento2.Functions.DiscouragedFunction -- locked gzip streams require checked native writes and descriptor ownership

/** Preserve the active log until a complete gzip archive has been verified. */
class Rotator
{
    /**
     * Archive a locked log with bounded memory, then truncate the original descriptor.
     *
     * @param string $path
     * @return string
     * @throws \RuntimeException
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     * @SuppressWarnings("PHPMD.NPathComplexity")
     */
    public function rotate(string $path): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction -- stream locking and checked writes preserve the source on failure
        $source = fopen($path, 'r+b');
        if ($source === false) {
            throw new \RuntimeException('Cannot open the feed log for rotation.');
        }
        $archive = false;
        $verification = false;
        try {
            $archivePath = $path . '.' . gmdate('Y-m-d-H-i-s') . '.' . bin2hex(random_bytes(8)) . '.gz';
            if (!flock($source, LOCK_EX)) {
                throw new \RuntimeException('Cannot lock the feed log for rotation.');
            }
            $stat = fstat($source);
            if ($stat === false) {
                throw new \RuntimeException('Cannot read the feed log size.');
            }
            $size = $stat['size'];
            $archive = $this->openArchive($archivePath);
            if ($archive === false) {
                throw new \RuntimeException('Cannot create the feed log archive.');
            }
            $filter = stream_filter_append($archive, 'zlib.deflate', STREAM_FILTER_WRITE, ['window' => 31]);
            if ($filter === false || stream_copy_to_stream($source, $archive) !== $size
                || !stream_filter_remove($filter) || !fflush($archive)
            ) {
                throw new \RuntimeException('Cannot complete the feed log archive.');
            }
            // phpcs:ignore Magento2.Functions.DiscouragedFunction -- verify the gzip stream before truncating the locked source
            $verification = fopen('compress.zlib://' . $archivePath, 'rb');
            if ($verification === false || !rewind($source)) {
                throw new \RuntimeException('Cannot verify the feed log archive.');
            }
            $sourceHash = hash_init('sha256');
            $archiveHash = hash_init('sha256');
            $sourceBytes = hash_update_stream($sourceHash, $source);
            $archiveBytes = hash_update_stream($archiveHash, $verification);
            if ($sourceBytes !== $size || $archiveBytes !== $size
                || !hash_equals(hash_final($sourceHash), hash_final($archiveHash))
            ) {
                throw new \RuntimeException('The feed log archive does not match the original.');
            }
            if (!ftruncate($source, 0)) {
                throw new \RuntimeException('Cannot truncate the archived feed log.');
            }
            return $archivePath;
        } catch (\Throwable $exception) {
            throw new \RuntimeException('Feed log rotation failed.', 0, $exception);
        } finally {
            if (is_resource($verification)) {
                fclose($verification);
            }
            if (is_resource($archive)) {
                fclose($archive);
            }
            flock($source, LOCK_UN);
            fclose($source);
        }
    }

    /**
     * Exclusively create the archive so an existing archive can never be overwritten.
     *
     * @param string $path
     * @return resource|false
     */
    protected function openArchive(string $path)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction -- exclusive stream creation is required for lossless rotation
        return fopen($path, 'x+b');
    }
}
