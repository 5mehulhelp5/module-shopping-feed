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

namespace MageOS\ShoppingFeed\Observer\Promotions;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class Uploader implements ObserverInterface
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Promotions\Provider
     */
    protected $provider;

    /**
     * @var \MageOS\ShoppingFeed\Model\Uploader\GzipCompressor
     */
    protected $gzipCompressor;

    public function __construct(
        \MageOS\ShoppingFeed\Model\Promotions\Provider $provider,
        \MageOS\ShoppingFeed\Model\Uploader\GzipCompressor $gzipCompressor
    ) {

        $this->provider = $provider;
        $this->gzipCompressor = $gzipCompressor;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if ($this->provider->isFileCreated()) {
            $generator = $observer->getEvent()->getData('generator');
            $uploader = $observer->getEvent()->getData('uploader');
            $upload = $observer->getEvent()->getData('upload');

            $file = $this->provider->getPromotionFile();
            $gzipFile = null;
            try {
                if ((bool) $upload->getGzip()) {
                    $gzipFile = $this->gzipCompressor->compress($file);
                    $file = $gzipFile;
                }
                $result = $uploader->upload($file);

                if ($result) {
                    $generator->getLogger()->info(sprintf("File %s uploaded to %s:%s", $file, $upload->getHost(), $upload->getPath()));
                } else {
                    $generator->getLogger()->warning(sprintf('Failed to upload %s', $file));
                }
            } finally {
                if ($gzipFile !== null) {
                    $this->gzipCompressor->remove($gzipFile);
                }
            }
        }
    }
}
