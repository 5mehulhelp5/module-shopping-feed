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

namespace MageOS\ShoppingFeed\Model\Logger\Formatter;


class DefaultLog extends \Monolog\Formatter\LineFormatter
{
    const SIMPLE_FORMAT = "[%extra.pid%][%datetime%] %level_name%: %message% %context% %extra%\n";

    public function format($record): string
    {
        $record['extra']['pid'] = getmypid();
        $output = parent::format($record);

        $newOutput =  str_replace('{} []', '', $output);
        if ($output == $newOutput) {
            $output =  str_replace('{}', '', $output);
            $output =  str_replace('[]', '', $output);
        } else {
            $output = $newOutput;
        }

        return $output;
    }
}
