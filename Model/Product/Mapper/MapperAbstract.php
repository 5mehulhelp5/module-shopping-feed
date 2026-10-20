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

namespace MageOS\ShoppingFeed\Model\Product\Mapper;

/**
 * Abstract class, defining main final methods
 *
 * Class MapperAbstract
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper
 */
abstract class MapperAbstract implements MapperInterface
{

    /**
     * Holds the adapter array
     *
     * @var array
     */
    protected $adapter = [];

    /**
     * Holds mapper configurations
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * @var \MageOS\ShoppingFeed\Model\Logger
     */
    protected $logger;

    /**
     * MapperAbstract constructor.
     *
     * @param \MageOS\ShoppingFeed\Model\Logger $logger
     */
    public function __construct(\MageOS\ShoppingFeed\Model\Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Adapter of the product being mapped (the most recently added one).
     *
     * @return \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract
     */
    public function getAdapter()
    {
        return $this->adapter[count($this->adapter) - 1];
    }

    /**
     * Push the adapter of the product to map; popAdapter() restores the previous one.
     *
     * @param  \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $adapter
     * @return \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract
     */
    public function addAdapter($adapter)
    {
        array_push($this->adapter, $adapter);
        return $this;
    }

    /**
     * Remove and return the most recently added adapter.
     *
     * @return \MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple
     */
    public function popAdapter()
    {
        return array_pop($this->adapter);
    }

    /**
     * Getter for mapper configuration
     *
     * @param  string $path
     * @return bool
     */
    public function getConfiguration($path)
    {
        return isset($this->configuration[$path]) ? $this->configuration[$path] : false;
    }

    /**
     * Setter for mapper configuration
     *
     * @param string $path
     * @param mixed  $value
     *
     * @return $this
     */
    public function setConfiguration($path, $value)
    {
        $this->configuration[$path] = $value;
        return $this;
    }

    /**
     * Whether the mapper configuration has a value for the path.
     *
     * @param string $path
     *
     * @return bool
     */
    public function hasConfiguration($path)
    {
        return array_key_exists($path, $this->configuration);
    }

    /**
     * Clear the mapper configuration.
     *
     * @return MapperAbstract
     */
    public function resetConfiguration()
    {
        $this->configuration = [];
        return $this;
    }

    /**
     * Whether the mapped cell value excludes the product from the feed; mappers with a filter override this.
     *
     * @param mixed $cell
     * @return boolean
     */
    public function filter($cell)
    {
        return false;
    }
}
