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

namespace MageOS\ShoppingFeed\Model\Uploader\Mode;

use \MageOS\ShoppingFeed\Model\Uploader\UploaderAbstract;
use \MageOS\ShoppingFeed\Model\Uploader\UploaderInterface;

/**
 * Ftp Uploader
 */
class Ftp extends UploaderAbstract implements UploaderInterface
{
    const DEFAULT_TIMEOUT = 10;

    /**
     * @var \Magento\Framework\Filesystem\Io\Ftp
     */
    protected $connection;

    /**
     * Ftp constructor.
     *
     * @param \MageOS\ShoppingFeed\Model\Feed\Upload $feedUpload
     * @param \Magento\Framework\Filesystem\Io\Ftp       $connection
     * @param array                                      $data
     */
    public function __construct(
        \MageOS\ShoppingFeed\Model\Feed\Upload $feedUpload,
        \Magento\Framework\Filesystem\Io\Ftp $connection,
        array $data = []
    ) {
        $this->connection = $connection;
        parent::__construct($feedUpload, $data);
    }

    /**
     * Provides connection configuration for connection class
     *
     * @param  $config
     * @return array
     */
    public function getConnectionConfiguration($config)
    {
        return [
            'host'     => $config['host'],
            'user'     => $config['username'],
            'password' => $config['password'],
            'port'     => $config['port'],
            'passive'  => $config['passive'],
            'timeout'  => self::DEFAULT_TIMEOUT,
        ];
    }
}
