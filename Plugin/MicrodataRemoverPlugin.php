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
 */
class MicrodataRemoverPlugin
{
    public const XML_PATH_ENABLED = 'mageos_shopping_feed/google/microdata_enabled';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    // phpcs:ignore Magento2.Commenting.ClassPropertyPHPDocFormatting.Missing -- @var adds new PHPStan false positives
    private $feedCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array<int, bool> Whether each store ID has a feed used for microdata
     */
    private array $selectedFeeds = [];

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \MageOS\ShoppingFeed\Model\ResourceModel\Feed\CollectionFactory $feedCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MageOS\ShoppingFeed\Model\ResourceModel\Feed\CollectionFactory $feedCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Turn off the native price schema markup when the store has a microdata feed
     *
     * @param \Magento\Framework\View\Element\Template $subject
     * @param string $interceptedFileName
     * @return array
     */
    public function beforeFetchView(\Magento\Framework\View\Element\Template $subject, $interceptedFileName)
    {
        if ($subject instanceof \Magento\Framework\Pricing\Render\Amount && $this->isEnabled()) {
            $subject->setData('schema', false);
        }

        return [$interceptedFileName];
    }

    /**
     * Remove native title/SKU attributes only when the current store has a replacement feed.
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $subject
     * @return void
     */
    public function beforeToHtml(\Magento\Framework\View\Element\AbstractBlock $subject): void
    {
        $attribute = [
            'page.main.title' => 'add_base_attribute',
            'product.info.sku' => 'add_attribute',
        ][$subject->getNameInLayout()] ?? null;
        if ($attribute !== null && $this->isEnabled()) {
            $subject->setData($attribute, '');
        }
    }

    /**
     * Check whether microdata is enabled and the current store has a feed used for microdata
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (!(bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return false;
        }
        $storeId = (int)$this->storeManager->getStore()->getId();
        if (!array_key_exists($storeId, $this->selectedFeeds)) {
            $this->selectedFeeds[$storeId] = $this->feedCollectionFactory->create()
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('use_microdata', 1)
                ->getSize() > 0;
        }
        return $this->selectedFeeds[$storeId];
    }
}
