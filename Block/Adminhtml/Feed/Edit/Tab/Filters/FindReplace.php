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
class FindReplace extends AbstractArrayElement implements RendererInterface
{
    const DEFAULT_COLUMN = 'id';

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns
     */
    protected $sourceProductColumns;

    /**
     * @var string
     */
    protected $_template = 'feed/edit/tab/filters/find-replace.phtml';

    /**
     * Columns cache
     *
     * @var array
     */
    protected $columns;

    /**
     * FindReplace constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                    $context
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns $sourceProductColumns
     * @param array                                                      $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns $sourceProductColumns,
        array $data = []
    ) {
        $this->sourceProductColumns = $sourceProductColumns;
        parent::__construct($context, $data);
    }

    /**
     * Sort find and replace rule values callback method
     *
     * @param                                   array $a
     * @param                                   array $b
     * @return                                  int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function sortValuesCallback($a, $b)
    {
        if ($a['find'] != $b['find']) {
            return $a['find'] < $b['find'] ? -1 : 1;
        }

        return 0;
    }

    /**
     * Retrieve allowed columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->sourceProductColumns->toOptionArray();
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
                'onclick' => 'return findReplaceControl.addItem()',
                'class' => 'add'
            ]
        );
        $button->setName('add_find_replace_rule_button');

        $this->setChild('add_button', $button);
        return $this->getChildHtml('add_button');
    }

    /**
     * Fixis the broken find and replace widget when backslash is used in inputs.
     *
     * @param  array|string $data
     * @param  string       $quote
     * @return array|mixed|string
     */
    public function escapeJsQuote($data, $quote = '\'')
    {
        $result = $this->_escaper->escapeJsQuote($data, $quote);
        $result = str_replace('\\', '\\\\', $result);
        $result = str_replace("'", "\'", $result);
        return $result;
    }
}
