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

namespace MageOS\ShoppingFeed\Model\Feed\Source\Shipping;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AvailableMethods
 */
class AvailableMethods implements OptionSourceInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingMethodConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $currencies;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Registry                        $registry
     * @param \Magento\Shipping\Model\Config                     $shippingMethodConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Shipping\Model\Config $shippingMethodConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->coreRegistry = $registry;
        $this->shippingMethodConfig = $shippingMethodConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $options = [['value' => '', 'label' => '']];

        $carriersRealtime = $this->getRealtimeCarriers();

        foreach ($this->shippingMethodConfig->getActiveCarriers() as $carrier) {
            $carrierCode = $carrier->getCarrierCode();

            if (in_array($carrierCode, $carriersRealtime)) {
                continue;
            }

            if (!$carrier->isActive()) {
                continue;
            }

            $carrierMethods = $carrier->getAllowedMethods();
            if (!is_array($carrierMethods) || !count($carrierMethods)) {
                continue;
            }

            $options[$carrierCode] = [
                'label' => $this->getCarrierName($carrierCode),
                'value' => [],
            ];

            foreach ($carrierMethods as $methodCode => $methodTitle) {
                $options[$carrierCode]['value'][] = [
                    'value' => sprintf('%s_%s', $carrierCode, $methodCode),
                    'label' => sprintf('[%s] %s', $carrierCode, $methodTitle),
                ];
            }
        }

        $this->options = $options;

        return $this->options;
    }

    /**
     * Get list of real time carriers
     *
     * @return array
     */
    protected function getRealtimeCarriers()
    {
        /* @var $model \MageOS\ShoppingFeed\Model\Feed */
        $feed = $this->coreRegistry->registry('feed');

        return (array) $feed->getConfig('shipping_carrier_realtime', []);
    }

    /**
     * Get carrier name by code
     *
     * @param  $carrierCode
     * @return mixed
     */
    protected function getCarrierName($carrierCode)
    {
        $carrierName = $carrierCode;

        if ($name = $this->scopeConfig->getValue(
            'carriers/' . $carrierCode . '/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            $carrierName = $name;
        }

        return $carrierName;
    }
}
