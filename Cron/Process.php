<?php
/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   MageOS_ShoppingFeed
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */
namespace MageOS\ShoppingFeed\Cron;

use MageOS\ShoppingFeed\Model\Exception as FeedException;
use MageOS\ShoppingFeed\Model\Logger;

class Process
{
    const XML_PATH_ENABLED = 'mageos_shopping_feed/general/cron_enabled';

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var resource
     */
    protected $lockFile;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue\Collection
     */
    protected $queueCollection;

    /**
     * @var int feedId to force generation of that feed.
     */
    protected $feedId = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var bool.
     * Is set to you when process is initiated through console and not magento's cron
     */
    protected $detached = false;


    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $file,
        \MageOS\ShoppingFeed\Model\Logger $logger,
        \MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue\Collection $queueCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {

        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->logger = $logger;
        $this->queueCollection = $queueCollection;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) ($this->scopeConfig->getValue(self::XML_PATH_ENABLED)) || $this->detached;
    }

    /**
     * Process feeds from queue
     *
     * @return bool True when there was no fatal processing failure.
     */
    public function execute()
    {
        if (!$this->isEnabled()) {
            return true;
        }

        if (is_null($this->feedId)) {
            $queue = $this->queueCollection->getQueue();
        } else {
            $queue = $this->queueCollection->getQueue($this->feedId);
            if (!$queue->getFeedId()) {
                $queue->setFeedId($this->feedId);
            }
        }

        if ($queue->getFeedId()) {
            $this->feedId = $queue->getFeedId();

            if ($this->acquireLock()) {
                $succeeded = true;
                $generator = null;
                try {
                    $queue->setRunning();
                    $generator = $queue->getGenerator();
                    $generator->run();
                } catch (FeedException $e) {
                    // Limits reached, batch has been set, do nothing here.
                    $this->logger->error($e->getMessage());
                } catch (\Throwable $e) {
                    $succeeded = false;
                    if ($generator !== null) {
                        $generator->updateStatus(\MageOS\ShoppingFeed\Model\Feed\Source\Status::STATUS_ERROR);
                    }
                    $this->logger->error($e->getMessage());
                } finally {
                    $this->releaseLock();
                }
                return $succeeded;
            }

            return false;
        } else {
            $this->logger->debug('Nothing in queue.');
        }

        return true;
    }

    /**
     * Fore feed generation for $feedId
     *
     * @param $feedId
     */
    public function setFeedId($feedId)
    {
        $this->feedId = $feedId;
        return $this;
    }

    public function setDetached()
    {
        $this->detached = true;
        return $this;
    }

    protected function getLockFile()
    {
        $id = !is_null($this->feedId) ? $this->feedId : 0;
        if (!$this->file->isDirectory($this->directoryList->getPath('tmp'))) {
            $this->file->createDirectory($this->directoryList->getPath('tmp'));
        }
        return $this->directoryList->getPath('tmp') . '/mageos_shopping_feed_'. $id . '.lock';
    }

    /**
     * Uses flock to lock generation of a feed in one process
     *
     * @return bool
     */
    public function acquireLock()
    {
        $lockFile = $this->getLockFile();
        try {
            $this->lockFile = $this->file->fileOpen($lockFile, "w");
        } catch (\Magento\Framework\Exception\FileSystemException) {
            $this->logger->error(sprintf('Can\'t create lock file %s, grant write permissions!', $lockFile));
            $this->closeLockFile();
            return false;
        }

        if (!is_resource($this->lockFile) || !$this->file->isExists($lockFile)) {
            $this->logger->error(sprintf('Can\'t create lock file %s, grant write permissions!', $lockFile));
            $this->closeLockFile();
            return false;
        }

        // If the location is not writable, flock() does not work and it doesn't mean another script instance is running
        if (!$this->file->isWritable($lockFile)) {
            $this->logger->error(sprintf('Path %s is not writable, grant write permissions!', $lockFile));
            $this->closeLockFile();
            return false;
        }

        try {
            $this->file->fileLock($this->lockFile, LOCK_EX | LOCK_NB);
        } catch (\Magento\Framework\Exception\FileSystemException) {
            $this->logger->debug(sprintf('Another process is generating the feed! Remove %s to continue.', $lockFile));
            $this->closeLockFile();
            return false;
        }

        $this->file->fileWrite($this->lockFile, date('Y-m-d H:i:s'));
        $this->file->fileFlush($this->lockFile); // flush output before releasing the lock
        return true;
    }

    /**
     * @return $this
     */
    public function releaseLock()
    {
        try {
            if (is_resource($this->lockFile)) {
                $this->file->fileUnlock($this->lockFile);
            }
        } finally {
            $this->closeLockFile();
        }
        return $this;
    }

    /**
     * Close the lock handle without attempting to unlock it.
     *
     * @return void
     */
    private function closeLockFile()
    {
        $lockFile = $this->lockFile;
        $this->lockFile = null;
        if (is_resource($lockFile)) {
            $this->file->fileClose($lockFile);
        }
    }
}
