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

    public function __construct()
    {
        $this->cacheFailureString = hash('sha256', time());
    }

    /**
     * @param  $arguments
     * @param  $value
     * @return $this
     */
    public function setCache($arguments, $value)
    {
        $path = $this->preparePath($arguments);

        $this->setInternalCache($this->cache, $path, $value);
        return $this;
    }

    /**
     * @param  $arguments
     * @param  null $defaultValue
     * @return mixed|null|string
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
     * @param  mixed $argv
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
