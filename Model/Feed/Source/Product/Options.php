<?php

namespace MageOS\ShoppingFeed\Model\Feed\Source\Product;

class Options
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory
     */
    protected $optionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option\Collection
     */
    protected $optionCollection;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionFactory
    ) {

        $this->registry = $registry;
        $this->optionFactory = $optionFactory;
    }

    public function toOptionArray()
    {
        $result = [];
        /**
 * @var \Magento\Catalog\Model\Product\Option $option
*/
        foreach ($this->getOptionCollection() as $option) {
            $result[] = [
                'value' => $option->getTitle(),
                'label' => sprintf('%s (%s)', $option->getTitle(), $option->getCountProducts())
            ];
        }

        return $result;
    }

    public function getOptionCollection()
    {
        if (is_null($this->optionCollection)) {
            $feed = $this->registry->registry('feed');
            /**
 * @var \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $optionCollection
*/
            $this->optionCollection = $this->optionFactory->create();
            $this->optionCollection->addTitleToResult($feed->getStoreId());
            $this->optionCollection->getSelect()->columns('COUNT(*) AS count_products')->group('HEX(default_option_title.title)');
        }

        return $this->optionCollection;
    }
}
