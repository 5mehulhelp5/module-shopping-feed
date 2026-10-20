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
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option\Collection|null
     */
    protected $optionCollection;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionFactory
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionFactory
    ) {

        $this->registry = $registry;
        $this->optionFactory = $optionFactory;
    }

    /**
     * Custom option titles of the current feed's store, with product counts.
     *
     * @return array<int,array<string,mixed>>
     */
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

    /**
     * Custom options grouped by title for the feed held in the registry.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Collection
     */
    public function getOptionCollection()
    {
        if ($this->optionCollection === null) {
            $feed = $this->registry->registry('feed');
            $this->optionCollection = $this->optionFactory->create();
            $this->optionCollection->addTitleToResult($feed->getStoreId());
            $this->optionCollection->getSelect()
                ->columns('COUNT(*) AS count_products')
                ->group('HEX(default_option_title.title)');
        }

        return $this->optionCollection;
    }
}
