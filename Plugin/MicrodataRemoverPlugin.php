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

namespace MageOS\ShoppingFeed\Plugin;

/**
 * Class MicrodataRemoverPlugin
 *
 * Plugin sets "schema" variable to "false" just before fetching price renderer view
 * to remove default Magento 2 Microdata price tags. Affected template is:
 *
 * vendor/magento/module-catalog/view/base/templates/product/price/amount/default.phtml
 *
 * @package MageOS\ShoppingFeed\Plugin
 */
class MicrodataRemoverPlugin
{
    const XML_PATH_ENABLED = 'mageos_shopping_feed/google/microdata_enabled';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Framework\Pricing\Render\Amount $subject
     * @param $interceptedFileName
     * @return array
     */
    public function beforeFetchView(\Magento\Framework\Pricing\Render\Amount $subject, $interceptedFileName)
    {
        if ($this->isEnabled()) {
            $subject->setData('schema', false);
        }

        return [$interceptedFileName];
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) ($this->scopeConfig->getValue(self::XML_PATH_ENABLED));
    }
}
