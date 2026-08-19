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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Filters;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Element\AbstractArrayElement;

/**
 * Adminhtml find and replace renderer
 */
class OutputLimit extends AbstractArrayElement implements RendererInterface
{
    const DEFAULT_COLUMN = 'column';

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns
     */
    protected $sourceProductColumnsLimit;

    /**
     * @var string
     */
    protected $_template = 'feed/edit/tab/filters/output-limit.phtml';

    /**
     * Columns cache
     *
     * @var array
     */
    protected $columns;

    /**
     * FindReplace constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                          $context
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns\Limit $sourceProductColumnsLimit
     * @param array                                                            $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns\Limit $sourceProductColumnsLimit,
        array $data = []
    ) {
        $this->sourceProductColumnsLimit = $sourceProductColumnsLimit;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve allowed columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->sourceProductColumnsLimit->toOptionArray();
    }

    /**
     * Retrieve default value for column
     *
     * @return int
     */
    public function getDefaultColumn()
    {
        return self::DEFAULT_COLUMN;
    }

    /**
     * Retrieve 'Add Rule' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label' => __('Add Rule'),
                'onclick' => 'return outputLimitControl.addItem()',
                'class' => 'add'
            ]
        );
        $button->setName('add_output_limit_rule_button');

        $this->setChild('add_button', $button);
        return $this->getChildHtml('add_button');
    }

    /**
     * Sort values
     *
     * @param  array $data
     * @return array
     */
    protected function sortValues($data)
    {
        // No need for sorting here.
        return $data;
    }

    /**
     * Sort values callback method
     *
     * @param                                   array $a
     * @param                                   array $b
     * @return                                  int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function sortValuesCallback($a, $b)
    {
        // dummy interface implamentation
        // No need for sorting here.
        return 0;
    }
}
