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

namespace MageOS\ShoppingFeed\Model\Uploader;

/**
 * Factory class for \MageOS\ShoppingFeed\Model\Uploader\Mode\*
 */
class UploaderFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Created instances, keyed by upload mode
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param  \MageOS\ShoppingFeed\Model\Feed\Upload $feedUpload
     * @return \MageOS\ShoppingFeed\Model\Uploader\UploaderInterface
     */
    public function create(
        \MageOS\ShoppingFeed\Model\Feed\Upload $feedUpload,
        $singleton = true
    ) {
        $mode = $feedUpload->getMode();

        if (!isset($this->instances[$mode]) || !$singleton) {
            $className = sprintf('MageOS\ShoppingFeed\Model\Uploader\Mode\%s', ucfirst($mode));
            if (!class_exists($className)) {
                $className = 'MageOS\ShoppingFeed\Model\Uploader\Mode\Ftp';
            }
            $object = $this->objectManager->create(
                $className, [
                'feedUpload'    => $feedUpload
                ]
            );
            if ($singleton) {
                $this->instances[$mode] = $object;
            } else {
                return $object;
            }
        } else {
            $this->instances[$mode]
                ->unsetData()
                ->setFeedUpload($feedUpload);
        }

        return $this->instances[$mode];
    }
}
