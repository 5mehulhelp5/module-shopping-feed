<?php

namespace MageOS\ShoppingFeed\Model\Generator;

/**
 * Class Batch
 *
 * @package MageOS\ShoppingFeed\Model\Generator
 *
 * @method $this   setEnabled(boolean $enabled)
 * @method $this   setOffset(int $offset)
 * @method $this   setLimit(int $limit)
 */
class Batch extends \Magento\Framework\DataObject
{
    /**
     * @return int
     */
    public function getLimit()
    {
        return ($this->hasData('limit') && $this->getData('limit') > 0) ? $this->getData('limit') : 1000;
    }


    public function isNew()
    {
        return $this->getOffset() == 0;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->hasData('enabled') && (bool)$this->getData('enabled');
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->hasData('offset') && is_int($this->getData('offset')) ? $this->getData('offset') : 0;
    }
}
