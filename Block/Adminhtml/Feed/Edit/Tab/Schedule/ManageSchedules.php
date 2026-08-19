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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Schedule;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Element\AbstractArrayElement;

/**
 * Adminhtml Manage Schedules renderer
 */
class ManageSchedules extends AbstractArrayElement implements RendererInterface
{
    const DEFAULT_START_AT = 0;
    const DEFAULT_BATCH_MODE = 0;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $sourceYesno;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Schedule\StartAt
     */
    protected $sourceStartAt;

    /**
     * @var string
     */
    protected $_template = 'feed/edit/tab/schedule/manage-schedules.phtml';

    /**
     * Columns cache
     *
     * @var array
     */
    protected $columns;

    /**
     * FindReplace constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                     $context
     * @param \Magento\Config\Model\Config\Source\Yesno                   $sourceYesno
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Schedule\StartAt $sourceStartAt
     * @param array                                                       $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        \MageOS\ShoppingFeed\Model\Feed\Source\Schedule\StartAt $sourceStartAt,
        array $data = []
    ) {
        $this->sourceYesno = $sourceYesno;
        $this->sourceStartAt = $sourceStartAt;
        parent::__construct($context, $data);
    }

    /**
     * Sort schedule values callback method
     *
     * @param                                   array $a
     * @param                                   array $b
     * @return                                  int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function sortValuesCallback($a, $b)
    {
        if ($a['start_at'] != $b['start_at']) {
            return $a['start_at'] < $b['start_at'] ? -1 : 1;
        }

        return 0;
    }

    /**
     * Retrieve Start At options
     *
     * @return array
     */
    public function getStartAtOptions()
    {
        return $this->sourceStartAt->toOptionArray();
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
     * Retrieve default value for Start At
     *
     * @return int
     */
    public function getDefaultStartAt()
    {
        return self::DEFAULT_START_AT;
    }

    /**
     * Retrieve default value for Batch Mode
     *
     * @return int
     */
    public function getDefaultBatchMode()
    {
        return self::DEFAULT_BATCH_MODE;
    }

    /**
     * Retrieve 'Add Schedule' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label' => __('Add Schedule'),
                'onclick' => 'return manageSchedulesControl.addItem()',
                'class' => 'add'
            ]
        );
        $button->setName('add_manage_schedules_button');

        $this->setChild('add_button', $button);
        return $this->getChildHtml('add_button');
    }
}
