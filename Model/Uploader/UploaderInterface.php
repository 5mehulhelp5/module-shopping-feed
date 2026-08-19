<?php

namespace MageOS\ShoppingFeed\Model\Uploader;

/**
 * Interface for Uploaders
 */
interface UploaderInterface
{
    /**
     * Uploads file
     *
     * @param  string $filePath
     * @return bool
     */
    public function upload($filePath);

    /**
     * Checks connection
     *
     * @return bool
     */
    public function checkConnection();
}
