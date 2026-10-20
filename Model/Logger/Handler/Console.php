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

use Monolog\LogRecord;

class Console extends \Monolog\Handler\AbstractProcessingHandler
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface|null
     */
    protected $output;

    /**
     * Use the memory log formatter at INFO level for console output.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setFormatter(new \MageOS\ShoppingFeed\Model\Logger\Formatter\MemoryLog());
        $this->setLevel(\Monolog\Logger::INFO);
    }

    /**
     * Set the console output that receives formatted records.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    public function setOutputInterface(\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Write the formatted record to the console output when one is set.
     *
     * @param LogRecord|array<string,mixed> $record Monolog 3 record, or array on Monolog 2
     * @return void
     */
    protected function write($record): void
    {
        if ($this->output instanceof \Symfony\Component\Console\Output\OutputInterface) {
            $this->output->write((string) $record['formatted']);
        }
    }
}
