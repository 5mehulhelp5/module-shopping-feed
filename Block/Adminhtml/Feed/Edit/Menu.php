<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit;

/** Keep the standard editor's menu markup separate from Nebula's menu cache. */
class Menu extends \Magento\Backend\Block\Menu
{
    public function getCacheKeyInfo()
    {
        return array_merge(parent::getCacheKeyInfo(), ['shopping-feed-standard-editor']);
    }
}
