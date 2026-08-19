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
    public function __construct()
    {
        $stream = fopen('php://memory', 'w');
        parent::__construct($stream);

        $this->setFormatter(new \MageOS\ShoppingFeed\Model\Logger\Formatter\MemoryLog());
    }

    public function getLogContent()
    {
        rewind($this->stream);
        return stream_get_contents($this->stream);
    }
}
