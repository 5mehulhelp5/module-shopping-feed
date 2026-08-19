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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Columns;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Element\AbstractArrayElement;

/**
 * Adminhtml Columns Map item renderer
 */
class ColumnsMap extends AbstractArrayElement implements RendererInterface
{
    const DEFAULT_COLUMN = 'id';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns
     */
    protected $sourceProductColumns;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\DirectivesAndAttributes
     */
    protected $sourceDirectivesAndAttributes;

    /**
     * @var null|\Magento\Framework\Json\Encoder
     */
    protected $jsonEncoder;

    /**
     * @var \MageOS\ShoppingFeed\Model\FeedTypes\Config
     */
    protected $feedTypesConfig;

    /**
     * @var \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Options\OptionsRendererFactory
     */
    protected $optionRendererFactory;

    /**
     * @var string
     */
    protected $_template = 'feed/edit/tab/columns/columns-map.phtml';

    /**
     * @var bool
     */
    protected $mapExistingColumns = false;

    /**
     * @var string
     */
    protected $controlName = 'columnsMapControl';

    /**
     * Columns cache
     *
     * @var array
     */
    protected $columns;

    /**
     * Attributes cache
     *
     * @var array
     */
    protected $attributes;

    /**
     * ColumnsMap constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                                                $context
     * @param \Magento\Framework\Registry                                                            $coreRegistry
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns                             $sourceProductColumns
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\DirectivesAndAttributes             $sourceDirectivesAndAttributes
     * @param \Magento\Framework\Json\EncoderInterface                                               $jsonEncoder
     * @param \MageOS\ShoppingFeed\Model\FeedTypes\Config                                        $feedTypesConfig
     * @param \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Options\OptionsRendererFactory $optionsRendererFactory
     * @param array                                                                                  $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns $sourceProductColumns,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\DirectivesAndAttributes $sourceDirectivesAndAttributes,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \MageOS\ShoppingFeed\Model\FeedTypes\Config $feedTypesConfig,
        \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Options\OptionsRendererFactory $optionsRendererFactory,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->sourceProductColumns = $sourceProductColumns;
        $this->sourceDirectivesAndAttributes = $sourceDirectivesAndAttributes;
        $this->jsonEncoder = $jsonEncoder;
        $this->feedTypesConfig = $feedTypesConfig;
        $this->optionsRendererFactory = $optionsRendererFactory;
        parent::__construct($context, $data);
    }

    /**
     * Enable mapping for existing columns only
     *
     * @param  bool $flag
     * @return \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Columns\ColumnsMap
     */
    public function setMapExistingColumns($flag)
    {
        $this->mapExistingColumns = $flag;
        return $this;
    }

    /**
     * Retrieve form element instance
     *
     * @return bool
     */
    public function getMapExistingColumns()
    {
        return $this->mapExistingColumns;
    }

    /**
     * Set control name
     *
     * @param  string $name
     * @return \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Columns\ColumnsMap
     */
    public function setControlName($name)
    {
        $this->controlName = $name;
        return $this;
    }

    /**
     * Retrieve form element instance
     *
     * @return bool
     */
    public function getControlName()
    {
        return $this->controlName;
    }

    /**
     * Sort replace empty rule values callback method
     *
     * @param                                   array $a
     * @param                                   array $b
     * @return                                  int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function sortValuesCallback($a, $b)
    {
        if ($a['order'] != $b['order']) {
            return $a['order'] < $b['order'] ? -1 : 1;
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
     * Retrieve allowed customer groups
     *
     * @return array
     */
    public function getDirectivesAndAttributes()
    {
        return $this->sourceDirectivesAndAttributes->toOptionArray();
    }

    public function configToJson()
    {
        $directivesConfig = [];

        /* @var $feed \MageOS\ShoppingFeed\Model\Feed */
        $feed = $this->coreRegistry->registry('feed');

        $feedType = $feed->getData('type');
        $directives = $this->feedTypesConfig->getDirectives($feedType);

        foreach ($directives as $key => $directive) {
            if (!isset($directive['renderer'])) {
                continue;
            }

            $optionsRenderer = $this->optionsRendererFactory->create($directive['renderer']);
            $optionsRenderer->setParam(isset($directive['param']) ? $directive['param'] : '');

            $directivesConfig[$key] = [
                'label'    => $directive['label'],
                'renderer' => $directive['renderer'],
                'param'    => isset($directive['param']) ? $directive['param'] : '',
                'template' => $optionsRenderer->toHtml(),
            ];
        }

        $configs = $feed->getConfig('columns_product_columns');
        if ($configs != null && is_array($configs) && count($configs) > 0) {
            foreach ($configs as $config) {
                $attribute = isset($config['attribute']) ? $config['attribute'] : null;
                if (array_key_exists($attribute, $directivesConfig)
                    && $directivesConfig[$attribute]['renderer'] != 'MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Options\Renderer\HelpMessage'
                    && array_key_exists('param', $config) && $config['param'] != $directivesConfig[$attribute]['param']
                ) {
                    // We have a value inside the DB, so need for default value to show!
                    $directivesConfig[$attribute]['param'] = $config['param'];
                }
            }
        }

        return $this->jsonEncoder->encode(
            [
            'directives' => $directivesConfig
            ]
        );
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
        $buttonLabel = $this->getMapExistingColumns() ? __('Add Rule') : __('Add Column');
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label' => $buttonLabel,
                'onclick' => sprintf('return %s.addItem()', $this->getControlName()),
                'class' => 'add'
            ]
        );
        $button->setName('add_rule_item_button');

        $this->setChild('add_button', $button);
        return $this->getChildHtml('add_button');
    }
}
