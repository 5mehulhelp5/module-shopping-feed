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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

/**
 * Class ImageLink
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple
 */
class ImageLink extends MapperAbstract
{
    /**
     * @param  array $params
     * @return mixed|string
     */
    public function map(array $params = [])
    {
        // @var $product Mage_Catalog_Model_Product
        $product = $this->getAdapter()->getProduct();
        $imageType = !empty($params['param']) ? $params['param'] : 'image';

        $image = $product->getData($imageType);
        if ($image != 'no_selection' && $image != "") {
            $cell = $this->getAdapter()->getData('images_url_prefix') . '/' . ltrim($image, '/');
        } else {
            $cell = '';
        }

        $this->getAdapter()->getFilter()->findAndReplace($cell, $params['column']);
        return $cell;
    }
}
