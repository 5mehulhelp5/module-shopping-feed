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


namespace MageOS\ShoppingFeed\Block\Adminhtml;

use Magento\Backend\Block\Widget\Container as Container;

/**
 * Adminhtml feed main block
 *
 * @package MageOS\ShoppingFeed\Block\Adminhtml
 */
class Feed extends Container
{
    /**
     * @var \MageOS\ShoppingFeed\Model\FeedTypes\Config
     */
    protected $feedTypesConfig;

    /**
     * Feed constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context           $context
     * @param \MageOS\ShoppingFeed\Model\FeedTypes\Config $feedTypesConfig
     * @param array                                           $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \MageOS\ShoppingFeed\Model\FeedTypes\Config $feedTypesConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->feedTypesConfig = $feedTypesConfig;
    }

    /**
     * Prepare button and gridCreate Grid , edit/add grid row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {

        $addButtonProps = [
            'id' => 'add_new_feed',
            'label' => __('Create New Feed'),
            'class' => 'add primary',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddFeedButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        /*$importButtonProps = [
            'id' => 'import_feed',
            'label' => __('Import Feed'),
            'onclick' => 'setLocation(\'' . $this->_getFeedImportUrl() . '\')',
            'class' => 'import',
        ];
        $this->buttonList->add('import', $importButtonProps);*/

        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @return array
     */
    protected function _getAddFeedButtonOptions()
    {
        $feedTypes = $this->feedTypesConfig->getAll();

        foreach ($feedTypes as $feedType) {
            if (array_key_exists('label', $feedType)) {
                $splitButtonOptions[] = [
                    'label' => __($feedType['label']),
                    'onclick' => "setLocation('" . $this->_getFeedCreateUrl($feedType['name']) . "')"
                ];
            }
        }

        return $splitButtonOptions;
    }

    /**
     *
     *
     * @param  string $type
     * @return string
     */
    protected function _getFeedCreateUrl($type)
    {
        return $this->getUrl(
            'mageos_shopping_feed/feed/new',
            ['type' => $type]
        );
    }

    /**
     * @return string
     */
    protected function _getFeedImportUrl()
    {
        return $this->getUrl(
            'mageos_shopping_feed/feed/import'
        );
    }
}
