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

class Upload extends AbstractModel
{
    public const OBSCURED_VALUE = '******';

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /** @var string|null Ciphertext retained across masked and repeated saves. */
    private $encryptedPassword;

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
        $this->_init(\MageOS\ShoppingFeed\Model\ResourceModel\Feed\Upload::class);
    }

    /**
     * Encrypts password before save
     *
     * A masked value retains the loaded ciphertext. Model data stays encrypted.
     *
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->getPassword() === self::OBSCURED_VALUE) {
            if ($this->encryptedPassword === null) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Enter a password for the new upload destination.')
                );
            }
            if ($this->encryptor->decrypt($this->encryptedPassword) === '') {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The saved upload password cannot be read. Enter it again.')
                );
            }
            $this->setPassword($this->encryptedPassword);
        } elseif ($this->getPassword() !== $this->encryptedPassword) {
            $this->setPassword($this->encryptor->encrypt((string)$this->getPassword()));
        }
        $this->encryptedPassword = (string)$this->getPassword();

        return parent::beforeSave();
    }

    /**
     * Remember the stored ciphertext without decrypting model data.
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->encryptedPassword = (string)$this->getData('password');
        return parent::_afterLoad();
    }

    /**
     * Decrypt the credential only at the transport boundary.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDecryptedPassword(): string
    {
        $password = $this->encryptor->decrypt((string)$this->getData('password'));
        if ($password === '') {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The saved upload password cannot be read. Enter it again.')
            );
        }
        return $password;
    }
}
