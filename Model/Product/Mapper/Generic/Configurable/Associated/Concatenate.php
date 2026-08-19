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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\Associated;

use \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Concatenate as SimpleConcatenate;

class Concatenate extends SimpleConcatenate
{
    protected function getAttributeValue($key, $attributeColumn, $values = [], $params = [])
    {
        $inheritColumnsFromParent = $this->getAdapter()
            ->getFeed()
            ->getConfig('configurable_associated_products', []);

        $values[$key] = '';
        if (in_array($attributeColumn, $inheritColumnsFromParent)) {
            $values[$key] = $this->getAdapter()->getParentAdapter()->getMapValue($params);
        } else {
            $values[$key] = $this->getAdapter()->getMapValue($params);
        }

        if (empty($values[$key])) {
            $values[$key] = $this->getAdapter()->mapEmptyValues($params);
        }

        return $values;
    }

    public function map(array $params = [])
    {
        if (strpos($params['param'], '{{variants}}') !== false) {
            $parentProduct = $this->getAdapter()->getParentAdapter()->getProduct();
            $attributes = $parentProduct->getTypeInstance()->getConfigurableAttributesAsArray($parentProduct);

            $values = [];
            foreach ($attributes as $k => $attribute) {
                $newParams = $this->buildMapParameters($attribute['attribute_code']);
                try {
                    $values = $this->getAttributeValue($k, $attribute['attribute_code'], $values, $newParams);
                } catch (\Exception $e) {
                    $this->logger->warning(
                        sprintf(
                            'Could not get value of configurable attribute {{%s}} in Concatenate directive.',
                            $attribute['attribute_code']
                        ), $newParams
                    );
                }
            }

            $separator = $this->getAdapter()->getFeed()->getConfig('configurable_attribute_merge_value_separator');
            $out = implode($separator, array_filter($values));
            $params['param'] = str_replace('{{variants}}', $out, $params['param']);

            preg_match_all('/\{\{(.*?)\}\}/is', $params['param'], $matches);
            if (!isset($matches[1]) || empty($matches[1])) {
                return $params['param'];
            }
        }

        return parent::map($params);
    }
}
