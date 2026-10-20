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

namespace MageOS\ShoppingFeed\Model\Logger\Handler;

class MemoryStream extends \Monolog\Handler\StreamHandler
{
    /**
     * Log into an in-memory stream with the memory log formatter.
     */
    public function __construct()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction -- php://memory stream for Monolog; not a filesystem path
        $stream = fopen('php://memory', 'w');
        parent::__construct($stream);

        $this->setFormatter(new \MageOS\ShoppingFeed\Model\Logger\Formatter\MemoryLog());
    }

    /**
     * Return everything written to the in-memory stream so far.
     *
     * @return string|false
     */
    public function getLogContent()
    {
        rewind($this->stream);
        // phpcs:ignore Magento2.Exceptions.TryProcessSystemResources,Magento2.Functions.DiscouragedFunction -- reads the handler's own php://memory stream
        return stream_get_contents($this->stream);
    }
}
