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

namespace MageOS\ShoppingFeed\Model\Feed\Source\Product;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\Product\Type as ProductType;

/**
 * Class DirectivesAndAttributes
 */
class DirectivesAndAttributes implements OptionSourceInterface
{
    /**
     * Directives and attributes cache
     *
     * @var array
     */
    protected $directivesAndAttributes;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \MageOS\ShoppingFeed\Model\FeedTypes\Config
     */
    protected $feedTypesConfig;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\Attributes
     */
    protected $sourceAttributes;

    /**
     * Types constructor.
     *
     * @param \Magento\Framework\Registry                                   $coreRegistry
     * @param \Magento\Eav\Model\Config                                     $eavConfig
     * @param \MageOS\ShoppingFeed\Model\FeedTypes\Config               $feedTypesConfig
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\Attributes $sourceAttributes
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Eav\Model\Config $eavConfig,
        \MageOS\ShoppingFeed\Model\FeedTypes\Config $feedTypesConfig,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\Attributes $sourceAttributes
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->eavConfig = $eavConfig;
        $this->feedTypesConfig = $feedTypesConfig;
        $this->sourceAttributes = $sourceAttributes;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->directivesAndAttributes !== null) {
            return $this->directivesAndAttributes;
        }

        $directiveOptions = [];
        foreach ($this->getDirectives() as $code => $directive) {
            $directiveOptions[] = [
                'label' => $directive['label'],
                'value' => $code,
            ];
        }

        $attributeOptions = [];
        foreach ($this->getAttributes() as $attribute) {
            $attribute['code'] = trim($attribute['code']);
            $attribute['label'] = trim($attribute['label']);
            $attributeOptions[] = [
                'label' => sprintf('%s (%s)', $attribute['label'], $attribute['code']),
                'value' => $attribute['code'],
            ];
        }

        return $this->directivesAndAttributes = [
            [
                'label' => 'Directives',
                'value' => $directiveOptions
            ],
            [
                'label' => 'Attributes',
                'value' => $attributeOptions
            ]
        ];
    }

    /**
     * Retrieve directives
     *
     * @return array
     */
    public function getDirectives()
    {
        /* @var $feed \MageOS\ShoppingFeed\Model\Feed */
        $feed = $this->coreRegistry->registry('feed');

        $feedType = $feed->getData('type');
        return $this->feedTypesConfig->getDirectives($feedType);
    }

    /**
     * Retrieve attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = $this->sourceAttributes->getAttributes();

        /* @var $feed \MageOS\ShoppingFeed\Model\Feed */
        $feed = $this->coreRegistry->registry('feed');

        $excludeAttributes = $feed->getConfig('columns_exclude_attributes', []);

        $attributes = array_filter(
            $attributes, function ($attribute) use ($excludeAttributes) {
                return !in_array($attribute['code'], $excludeAttributes);
            }
        );

        return $attributes;
    }
}
