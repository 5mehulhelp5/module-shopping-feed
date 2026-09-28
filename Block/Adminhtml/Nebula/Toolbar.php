<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Block\Adminhtml\Nebula;

class Toolbar extends \Magento\Backend\Block\Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        private \MageOS\ShoppingFeed\Model\Feed\Source\Type $types,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getTypes(): array
    {
        return $this->_authorization->isAllowed('MageOS_ShoppingFeed::save')
            ? $this->types->getOptionArray() : [];
    }
}
