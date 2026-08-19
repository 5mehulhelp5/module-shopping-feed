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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Uploads;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Element\AbstractArrayElement;

/**
 * Adminhtml Manage Uploads renderer
 */
class ManageUploads extends AbstractArrayElement implements RendererInterface
{
    const DEFAULT_TRANSFER_MODE = 'sftp';
    const DEFAULT_GZIP = 0;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $sourceYesno;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Uploads\TransferModes
     */
    protected $sourceTransferModes;

    /**
     * @var string
     */
    protected $_template = 'feed/edit/tab/uploads/manage-uploads.phtml';

    /**
     * ManageUploads constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                          $context
     * @param \Magento\Config\Model\Config\Source\Yesno                        $sourceYesno
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Uploads\TransferModes $sourceTransferModes
     * @param array                                                            $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        \MageOS\ShoppingFeed\Model\Feed\Source\Uploads\TransferModes $sourceTransferModes,
        array $data = []
    ) {
        $this->sourceYesno = $sourceYesno;
        $this->sourceTransferModes = $sourceTransferModes;
        parent::__construct($context, $data);
    }

    /**
     * Sort uploads values callback method
     *
     * @param                                   array $a
     * @param                                   array $b
     * @return                                  int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function sortValuesCallback($a, $b)
    {
        if ($a['host'] != $b['host']) {
            return $a['host'] < $b['host'] ? -1 : 1;
        }

        return 0;
    }

    /**
     * Retrieve Transfer Mode options
     *
     * @return array
     */
    public function getTransferModes()
    {
        return $this->sourceTransferModes->toOptionArray();
    }

    /**
     * Retrieve Yes/No options
     *
     * @return array
     */
    public function getYesnoOptions()
    {
        return $this->sourceYesno->toOptionArray();
    }

    /**
     * Retrieve default value for Mode
     *
     * @return int
     */
    public function getDefaultTransferMode()
    {
        return self::DEFAULT_TRANSFER_MODE;
    }

    /**
     * Retrieve default value for Gzip
     *
     * @return int
     */
    public function getDefaultGzip()
    {
        return self::DEFAULT_GZIP;
    }

    /**
     * Retrieve 'Add Account' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label' => __('Add Account'),
                'onclick' => 'return manageUploadsControl.addItem()',
                'class' => 'add'
            ]
        );
        $button->setName('add_manage_uploads_button');

        $this->setChild('add_button', $button);
        return $this->getChildHtml('add_button');
    }
}
