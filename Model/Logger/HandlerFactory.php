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

namespace MageOS\ShoppingFeed\Model\Logger;

class HandlerFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string                                    $instanceName
     */
    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->directoryList = $directoryList;
        $this->_objectManager = $objectManager;
    }

    /**
     * @param  string $filePath
     * @param  int    $level
     * @return \Magento\Framework\Logger\Handler\Base
     */
    public function create($filePath = '', $level = \Monolog\Logger::INFO)
    {
        $mageRootPath = $this->directoryList->getRoot();
        $filePath = $mageRootPath . $filePath;

        /**
 * @var \Magento\Framework\Logger\Handler\Base $handler
*/
        $handler = $this->_objectManager->create(
            'Magento\Framework\Logger\Handler\Base', [
            'filePath' => $filePath
            ]
        );

        $handler->setLevel($level);
        $handler->setFormatter(new Formatter\DefaultLog());
        return $handler;
    }
}
