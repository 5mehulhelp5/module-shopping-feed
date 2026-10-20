<?php

namespace MageOS\ShoppingFeed\Model\Generator;

class Batch extends \Magento\Framework\DataObject
{
    /**
     * Batch size, defaulting to 1000 when unset or not positive.
     *
     * @return int
     */
    public function getLimit()
    {
        return ($this->hasData('limit') && $this->getData('limit') > 0) ? $this->getData('limit') : 1000;
    }

    /**
     * Whether this batch starts at the first product.
     *
     * @return bool
     */
    public function isNew()
    {
        return $this->getOffset() == 0;
    }

    /**
     * Whether batch processing is enabled.
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->hasData('enabled') && (bool)$this->getData('enabled');
    }

    /**
     * Number of products already processed, defaulting to 0.
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->hasData('offset') && is_int($this->getData('offset')) ? $this->getData('offset') : 0;
    }
}
