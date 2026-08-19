<?php

namespace MageOS\ShoppingFeed\Model\Uploader;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;

class GzipCompressor
{
    private const CHUNK_SIZE = 1048576;

    /**
     * @var File
     */
    private $fileDriver;

    public function __construct(File $fileDriver)
    {
        $this->fileDriver = $fileDriver;
    }

    /**
     * Create a gzip file beside the source without loading the full feed into memory.
     *
     * @param string $sourcePath
     * @return string
     * @throws LocalizedException
     */
    public function compress($sourcePath)
    {
        $targetPath = $sourcePath . '.gz';
        $temporaryPath = $targetPath . '.tmp';
        $sourceHandle = null;
        $gzipHandle = null;

        $this->remove($temporaryPath);
        $this->remove($targetPath);

        try {
            $sourceHandle = $this->fileDriver->fileOpen($sourcePath, 'rb');
            $gzipHandle = gzopen($temporaryPath, 'wb9');
            if ($gzipHandle === false) {
                throw new LocalizedException(__('Unable to create gzip file %1.', $temporaryPath));
            }

            while (!$this->fileDriver->endOfFile($sourceHandle)) {
                $chunk = $this->fileDriver->fileRead($sourceHandle, self::CHUNK_SIZE);
                if ($chunk !== '' && gzwrite($gzipHandle, $chunk) === false) {
                    throw new LocalizedException(__('Unable to write gzip file %1.', $temporaryPath));
                }
            }

            $this->fileDriver->fileClose($sourceHandle);
            $sourceHandle = null;
            gzclose($gzipHandle);
            $gzipHandle = null;
            $this->fileDriver->rename($temporaryPath, $targetPath);
        } catch (\Throwable $exception) {
            if (is_resource($sourceHandle)) {
                $this->fileDriver->fileClose($sourceHandle);
            }
            if (is_resource($gzipHandle)) {
                gzclose($gzipHandle);
            }
            $this->remove($temporaryPath);
            $this->remove($targetPath);
            throw $exception;
        }

        return $targetPath;
    }

    /**
     * Remove a generated gzip artifact when it is no longer needed.
     *
     * @param string $path
     * @return void
     */
    public function remove($path)
    {
        if ($this->fileDriver->isExists($path)) {
            $this->fileDriver->deleteFile($path);
        }
    }
}
