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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Categories;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Element\AbstractArrayElement;

/**
 * Adminhtml category taxonomy renderer
 */
class CategoryTaxonomy extends AbstractArrayElement implements RendererInterface
{
    /**
     * Category collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryProvider;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \MageOS\ShoppingFeed\Model\Taxonomy\ProviderFactory
     */
    protected $taxonomyProviderFactory;

    /**
     * @var string
     */
    protected $_template = 'feed/edit/tab/categories/category-taxonomy.phtml';

    /**
     * CategoryTaxonomy constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                         $context
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryProvider
     * @param \Magento\Framework\Registry                                     $coreRegistry
     * @param \MageOS\ShoppingFeed\Model\Taxonomy\ProviderFactory         $taxonomyProviderFactory
     * @param array                                                           $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \MageOS\ShoppingFeed\Model\Product\Category\CollectionProvider $categoryProvider,
        \Magento\Framework\Registry $coreRegistry,
        \MageOS\ShoppingFeed\Model\Taxonomy\ProviderFactory $taxonomyProviderFactory,
        array $data = []
    ) {
        $this->categoryProvider = $categoryProvider;
        $this->coreRegistry = $coreRegistry;
        $this->taxonomyProviderFactory = $taxonomyProviderFactory;
        parent::__construct($context, $data);
    }

    /**
     * Sort values - extended to preserve keys.
     *
     * @param  array $data
     * @return array
     */
    protected function sortValues($data)
    {
        uasort($data, [$this, 'sortValuesCallback']);
        return $data;
    }

    /**
     * Sort values callback method
     *
     * @param                                   array $a
     * @param                                   array $b
     * @return                                  int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function sortValuesCallback($a, $b)
    {
        if ($a['id'] != $b['id']) {
            return $a['id'] < $b['id'] ? -1 : 1;
        }

        return 0;
    }

    /**
     * Get categories for a feed store view
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategories()
    {
        /* @var $feed \MageOS\ShoppingFeed\Model\Feed */
        $feed = $this->coreRegistry->registry('feed');
        $categories = $this->categoryProvider->getCategories($feed);

        array_walk(
            $categories, function (&$category) {
                $category = array_merge($category, $this->getTaxonomyValues($category['id']));
            }
        );

        return $categories;
    }

    /**
     * Get existing taxonomy values for given category.
     *
     * Return empty template if it doesn't exist.
     *
     * @param  $categoryId
     * @return array
     */
    public function getTaxonomyValues($categoryId)
    {
        $taxonomyMappings = $this->getValues();

        if (isset($taxonomyMappings[$categoryId])) {
            $taxonomyMapping = $taxonomyMappings[$categoryId];
            return [
                'taxonomy' => $taxonomyMapping['tx'],
                'type'     => $taxonomyMapping['ty'],
                'enabled'  => $taxonomyMapping['d'], // Keep d key here for backword compatibility
                'priority' => $taxonomyMapping['p'],
            ];
        }

        return [
            'taxonomy' => '',
            'type'     => '',
            'enabled'  => 1,
            'priority' => '',
        ];
    }

    /**
     * Formats category path into format which can be used in HTML class name.
     *
     * @param  $path
     * @return mixed
     */
    public function formatCategoryPath($path)
    {
        return str_replace('/', '_', $path);
    }

    /**
     * Formats parent category path into format which can be used in HTML class name.
     *
     * @param  $path
     * @return mixed
     */
    public function formatParentCategoryPath($path)
    {
        return str_replace('/', '_', dirname($path));
    }

    /**
     * Get options for suggest widget
     *
     * @return array
     */
    public function getSelectorOptions()
    {
        return [
            'className' => 'category-taxonomy-select',
            'showRecent' => true,
            'storageKey' => 'category-taxonomy-key',
            'minLength' => 3,
            'currentlySelected' => '',
            'source' => $this->getTaxonomyOptions()
        ];
    }

    /**
     * @return array
     */
    public function getTaxonomyOptions()
    {
        /* @var $feed \MageOS\ShoppingFeed\Model\Feed */
        $feed = $this->coreRegistry->registry('feed');

        $taxonomyProvider = $this->taxonomyProviderFactory->create($feed);

        return $taxonomyProvider ? $taxonomyProvider->getTaxonomyList() : [];
    }

    /**
     * @return mixed
     */
    public function isTaxonomyAutocompleteEnabled()
    {
        $feed = $this->coreRegistry->registry('feed');

        return $feed->isTaxonomyAutocompleteEnabled();
    }
}
