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
 * @copyright Copyright (c) 2025 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Options\Renderer;

use Magento\Framework\View\Element\Template;

/**
 * Adminhtml VariantAttributes directive options renderer
 */
class StockAttribute extends Template
{
    /**
     * @var \Magento\CatalogInventory\Model\StockRegistryProvider
     */
    private $stockRegistryProvider;

    /**
     * @var string
     */
    protected $_template = 'feed/edit/form/options/renderer/stock-attribute.phtml';


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Model\StockRegistryProvider $stockRegistryProvider,
        array $data = []
    ) {
        $this->stockRegistryProvider = $stockRegistryProvider;
        parent::__construct($context, $data);
    }

    public function getAttributeOptions()
    {
        $stockItem = $this->stockRegistryProvider->getStockItem(1, 1);
        $stockAttributes = $stockItem->getData();

        $options = [];
        foreach ($stockAttributes as $code => $value) {
            // Skip internal/complex attributes
            if (is_object($value) || is_array($value) || substr($code, -3) === '_id') {
                continue;
            }

            $options[] = [
                'value' => $code,
                'label' => __($this->formatLabel($code))
            ];
        }

        return $options;
    }

    private function formatLabel($code)
    {
        return ucwords(str_replace('_', ' ', $code));
    }
}
