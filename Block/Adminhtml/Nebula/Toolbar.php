<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Block\Adminhtml\Nebula;

class Toolbar extends \Magento\Backend\Block\Template
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Type $types
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        private \MageOS\ShoppingFeed\Model\Feed\Source\Type $types,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get the feed types the admin user may create
     *
     * @return array<mixed>
     */
    public function getTypes(): array
    {
        return $this->_authorization->isAllowed('MageOS_ShoppingFeed::save')
            ? $this->types->getOptionArray() : [];
    }
}
