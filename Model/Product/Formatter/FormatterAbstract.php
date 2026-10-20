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
     * Holds the adapter object; an empty array until setAdapter() is called
     *
     * @var \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract|array
     */
    protected $adapter = [];

    /**
     * @var string|null
     */
    protected $column = null;

    /**
     * Adapter of the product being formatted.
     *
     * @return \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Bind the adapter of the product being formatted.
     *
     * @param  \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $adapter
     * @return \MageOS\ShoppingFeed\Model\Product\Formatter\FormatterAbstract
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Set the column being formatted, used to apply its find/replace rules.
     *
     * @param string|null $column
     * @return $this
     */
    public function setColumn($column)
    {
        $this->column = $column;
        return $this;
    }

    /**
     * Column being formatted.
     *
     * @return string|null
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Return the value unchanged; formatters override this.
     *
     * @param mixed $var Cell value
     * @return mixed
     */
    public function run($var)
    {
        return $var;
    }
}
