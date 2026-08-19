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

namespace MageOS\ShoppingFeed\Model\Product\Formatter;

/**
 * Abstract class, defining main final methods
 *
 * Class FormatterAbstract
 *
 * @package MageOS\ShoppingFeed\Model\Product\Formatter
 */
abstract class FormatterAbstract implements FormatterInterface
{
    /**
     * Holds the mapper object
     *
     * @var array
     */
    protected $adapter = [];

    /**
     * @var null
     */
    protected $column = null;

    /**
     * @return \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param  \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract
     * @return \MageOS\ShoppingFeed\Model\Product\Formatter\FormatterAbstract
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @param  $column
     * @return $this
     */
    public function setColumn($column)
    {
        $this->column = $column;
        return $this;
    }

    /**
     * @return |null
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param  $var
     * @return mixed
     */
    public function run($var)
    {
        return $var;
    }
}
