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

/**
 * Class Upload
 *
 * @package MageOS\ShoppingFeed\Model\Feed
 */
class Upload extends AbstractModel
{
    const OBSCURED_VALUE = '******';

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * Event prefix for observer
     *
     * @var string
     */
    protected $_eventPrefix = 'mageos_shopping_feed_feed_upload';

    /**
     * Upload constructor.
     *
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Encryption\EncryptorInterface             $encryptor
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->encryptor = $encryptor;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageOS\ShoppingFeed\Model\ResourceModel\Feed\Upload');
    }

    /**
     * Encrypts password before save
     *
     * It also reverts password to one from original data if it still set to
     * obscured value. This means that user didn't change password in the frontend.
     *
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->getPassword() === self::OBSCURED_VALUE) {
            $this->setPassword($this->getOrigData('password'));
        } else {
            $this->setPassword($this->encryptor->encrypt($this->getPassword()));
        }

        return parent::beforeSave();
    }

    /**
     * Decrypts password after load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->setData('password', $this->encryptor->decrypt($this->getData('password')));

        return parent::_afterLoad();
    }
}
