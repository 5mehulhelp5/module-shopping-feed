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

namespace MageOS\ShoppingFeed\Model\Generator;

class Cache
{
    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @var string
     */
    protected $cacheFailureString = '';

    /**
     * Create the sentinel value that marks a cache miss.
     */
    public function __construct()
    {
        $this->cacheFailureString = hash('sha256', time());
    }

    /**
     * Store a value under a key path built from the arguments.
     *
     * @param mixed $arguments key or list of keys; "/" separates path segments
     * @param mixed $value
     * @return $this
     */
    public function setCache($arguments, $value)
    {
        $path = $this->preparePath($arguments);

        $this->setInternalCache($this->cache, $path, $value);
        return $this;
    }

    /**
     * Read the value stored under a key path built from the arguments.
     *
     * @param mixed $arguments key or list of keys; "/" separates path segments
     * @param mixed $defaultValue returned when nothing is cached under $arguments
     * @return mixed
     */
    public function getCache($arguments, $defaultValue = null)
    {
        $path = $this->preparePath($arguments);

        $value = $this->getInternalCache($this->cache, $path);
        if ($value == $this->cacheFailureString) {
            $value = $defaultValue;
        }
        return $value;
    }

    /**
     * Walk the nested cache along the path; return the miss sentinel when absent.
     *
     * @param array $cache
     * @param array $path
     * @return mixed
     */
    protected function getInternalCache(array &$cache, array $path)
    {
        $partial = array_shift($path);
        if (count($path) == 0 && isset($cache[$partial])) {
            return  $cache[$partial];
        }

        if (isset($cache[$partial]) && is_array($cache[$partial])) {
            return $this->getInternalCache($cache[$partial], $path);
        }

        return $this->cacheFailureString;
    }

    /**
     * Write the value into the nested cache along the path.
     *
     * @param array $cache
     * @param array $path
     * @param mixed $value
     * @return void
     */
    protected function setInternalCache(array &$cache, array $path, $value)
    {
        $partial = array_shift($path);
        if (count($path) == 0) {
            $cache[$partial] = $value;
        } else {
            if (isset($cache[$partial]) && is_array($cache[$partial])) {
                $internalCache = $cache[$partial];
            } else {
                $internalCache = [];
            }
            $this->setInternalCache($internalCache, $path, $value);
            $cache[$partial] = $internalCache;
        }
    }

    /**
     * Split the arguments into cache path segments.
     *
     * @param mixed $arguments
     * @return array
     */
    protected function preparePath($arguments)
    {
        $path = [];
        $arguments = is_array($arguments) ? $arguments : [$arguments];
        foreach ($arguments as $parameter) {
            $parameters = explode('/', (string)$parameter);
            foreach ($parameters as $param) {
                $path[] = $param;
            }
        }
        return $path;
    }
}
