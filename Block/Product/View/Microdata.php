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

namespace MageOS\ShoppingFeed\Block\Product\View;

class Microdata extends \Magento\Catalog\Block\Product\AbstractProduct
{

    const XML_PATH_ENABLED              = 'mageos_shopping_feed/google/microdata_enabled';


    /**
     * @var \MageOS\ShoppingFeed\Model\MicrodataFactory
     */
    protected $microdataFactory;

    /**
     * Product Factory instance.
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Tax Helper instance.
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \MageOS\ShoppingFeed\Model\Microdata $microData
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \MageOS\ShoppingFeed\Model\MicrodataFactory $microdataFactory,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->taxHelper = $context->getTaxData();
        $this->productFactory = $productFactory;
        $this->microdataFactory = $microdataFactory;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) ($this->_scopeConfig->getValue(self::XML_PATH_ENABLED));
    }


    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function getMicrodata()
    {
        $product = $this->getProduct();
        $microdata = null;

        if ($this->isEnabled() && $product && $product->getId()) {
            try {
                $microdata = $this->getModel()->getMicrodata();
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }

        return $microdata;
    }

    protected function getModel()
    {
        $store = $this->_storeManager->getStore();

        $assocId = $this->getRequest()->getParam('aid', false);
        $product = $assocId === false ? $this->getProduct() : $this->productFactory->create()->load($assocId);

        return $this->microdataFactory->create([
            'product'                => $product,
            'block_product'          => $this->getProduct(),
            'store'                  => $store,
            'assoc_id'               => $assocId,
            'request_params'         => $this->getRequest()->getParams()
        ]);
    }
}
