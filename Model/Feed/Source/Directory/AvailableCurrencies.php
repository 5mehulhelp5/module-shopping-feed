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

namespace MageOS\ShoppingFeed\Model\Feed\Source\Directory;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Locale\Bundle\CurrencyBundle as CurrencyBundle;

/**
 * Class AvailableCurrencies
 */
class AvailableCurrencies implements OptionSourceInterface
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Currency factory
     *
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Framework\Locale\Bundle\CurrencyBundle
     */
    protected $currencyBundle;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $localeResolver;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $currencies;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface      $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory        $currencyFactory
     * @param \Magento\Framework\Locale\Bundle\CurrencyBundle $currency
     * @param \Magento\Framework\Locale\Resolver              $localeResolver
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Locale\Bundle\CurrencyBundle $currencyBundle,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\Registry $registry
    ) {
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->currencyBundle = $currencyBundle;
        $this->localeResolver = $localeResolver;
        $this->registry = $registry;
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

        $currencies = $this->getCurrencies();
        $options = [];
        foreach ($currencies as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        $this->options = $options;

        return $this->options;
    }

    /**
     * Retrieve currencies array
     * Return array: code => currency name
     * Return empty array if only one currency
     *
     * @return array
     */
    public function getCurrencies()
    {
        $currencies = $this->currencies;
        if ($currencies === null) {
            $currencies = [];
            $feed = $this->registry->registry('feed');

            $storeId = ($feed && $feed->getStoreId()) ? $feed->getStoreId() : 0;
            $codes = $this->storeManager->getStore($storeId)->getAvailableCurrencyCodes(true);

            if (is_array($codes) && count($codes) > 0) {
                $rates = $this->currencyFactory->create()->getCurrencyRates(
                    $this->storeManager->getStore($storeId)->getBaseCurrency(),
                    $codes
                );

                foreach ($codes as $code) {
                    if (empty($rates) || isset($rates[$code])) {
                        $allCurrencies = $this->currencyBundle->get(
                            $this->localeResolver->getLocale()
                        )['Currencies'];
                        $currencies[$code] = $allCurrencies[$code][1] ?: $code;
                    }
                }
            }

            $this->currencies = $currencies;
        }
        return $currencies;
    }
}
