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


namespace MageOS\ShoppingFeed\Model\Feed;

use Magento\Framework\Model\AbstractModel;

class Config extends AbstractModel
{
    /**
     * @var null|\Magento\Framework\Json\Encoder
     */
    protected $jsonEncoder = null;

    /**
     * @var null|\Magento\Framework\Json\Decoder
     */
    protected $jsonDecoder = null;



    /**
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
     * @param \Magento\Framework\Json\EncoderInterface                $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface                $jsonDecoder
     * @param array                                                   $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageOS\ShoppingFeed\Model\ResourceModel\Feed\Config');
    }

    /**
     * Json encode array before save if needed
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getData('value');
        if (is_array($value)) {
            $value = $this->jsonEncoder->encode($value);
            $this->setData('value', $value);
        }
        return parent::beforeSave();
    }

    /**
     * Json decode array after load if needed
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $value = $this->getData('value');
        if ($value) {
            $startsWith = strlen($value) ? $value[0] : '';
            if (in_array($startsWith, ['[', '{']) && ($newValue = $this->jsonDecoder->decode($value)) !== false) {
                $this->setData('value', $newValue);
            }
        }
        return parent::_afterLoad();
    }
}
