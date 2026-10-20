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

namespace MageOS\ShoppingFeed\Model\FeedTypes;

class Config extends \Magento\Framework\Config\Data
{
    /**
     * Get full configuration of feed type by name
     *
     * @param  string $name
     * @return array
     */
    public function getFeed($name)
    {
        return $this->get('feed/' . $name, []);
    }

    /**
     * Get full configuration of all registered feed types
     *
     * @return array
     */
    public function getAll()
    {
        return $this->get('feed');
    }

    /**
     * Check if directive is allowed for feed type.
     *
     * @param string $typeId
     * @param string $code
     * @return bool
     */
    public function isAllowedDirective($typeId, $code)
    {
        $directive = $this->get('feed/' . $typeId . '/directives/' . $code, false);

        if (is_array($directive)) {
            return true;
        }
        return false;
    }

    /**
     * Get one directive definition of a feed type, or an empty array.
     *
     * @param string $typeId
     * @param string $code
     * @return mixed
     */
    public function getDirective($typeId, $code)
    {
        return $this->get('feed/' . $typeId . '/directives/' . $code, []);
    }

    /**
     * Get all directive definitions of a feed type keyed by code, or an empty array.
     *
     * @param string $typeId
     * @return mixed
     */
    public function getDirectives($typeId)
    {
        return $this->get('feed/' . $typeId . '/directives', []);
    }

    /**
     * Get taxonomy provider class for given feed type
     *
     * @param string $typeId
     * @return array|mixed|null
     */
    public function getTaxonomyProvider($typeId)
    {
        return $this->get('feed/' . $typeId . '/taxonomyProvider', '');
    }
}
