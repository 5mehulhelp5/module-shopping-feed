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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Grouped\Associated;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

/**
 * Creates Product url and appends suffix if its set
 *
 * Class Url
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Grouped\Associated
 */
class Url extends MapperAbstract
{
    /**
     * @param  array $params
     * @return string
     */
    public function map(array $params = [])
    {
        $adapter = $this->getAdapter()->getParentAdapter();
        // @var $product \Magento\Catalog\Model\Product
        $product = $adapter->getProduct();

        $urlQuery = array_key_exists('param', $params) ? $params['param'] : '';
        if (substr($urlQuery, 0, 1) == '?') {
            $urlQuery = substr($urlQuery, 1);
        }

        $url = $product->getProductUrl();
        $pieces = parse_url($product->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK));

        $uniqueParams = ['prod_id' => $this->getAdapter()->getProduct()->getId()];
        $urlQuery .= (empty($urlQuery) ? '' : '&') . http_build_query($uniqueParams);

        if (strpos($url, $pieces['host']) === false) {
            $url = $pieces['scheme'] . '://' . $pieces['host'] . $url;
        } else {
            $pieces = parse_url($url);
            $url = $pieces['scheme'] . '://' . $pieces['host'] . $pieces['path'];
        }

        if (!empty($urlQuery) && substr($urlQuery, 0, 1) != '?') {
            $urlQuery = '?' . $urlQuery;
        }
        $cell = $url . $urlQuery;
        $adapter->getFilter()->findAndReplace($cell, $params['column']);
        return $cell;
    }
}
