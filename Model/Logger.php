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

namespace MageOS\ShoppingFeed\Model;

class Logger extends \Monolog\Logger
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Logger\HandlerFactory
     */
    protected $handlerFactory;

    public function __construct(
        \MageOS\ShoppingFeed\Model\Logger\HandlerFactory $handlerFactory,
        $logPrefix = 'MAGEOS_SHOPPING_FEED',
        array $handlers = [],
        array $processors = []
    ) {

        $this->handlerFactory = $handlerFactory;
        parent::__construct($logPrefix, $handlers, $processors);

        // Clear all handlers, we don't want to write to system.log
        $this->handlers = [];
    }

    /**
     * Adds log handler (file to log into)
     *
     * @param  $path
     * @param  int $level
     * @return $this
     */
    public function addHandler($path, $level = \Monolog\Logger::INFO)
    {
        $path = '/' . ltrim($path, '/');

        // Add specified handler
        $this->handlers[] = $this->handlerFactory->create($path, $level);

        return $this;
    }

    /**
     * Sets log handler only to default + given
     *
     * @param $path
     * @param int $level
     */
    public function setHandler($path, $level = \Monolog\Logger::INFO)
    {
        $this->handlers = [];
        $this->addHandler($path, $level);

        return $this;
    }

    public function resetHandler()
    {
        $this->handlers = [];
        return $this;
    }
}
