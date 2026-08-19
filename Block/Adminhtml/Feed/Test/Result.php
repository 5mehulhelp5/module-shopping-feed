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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Test;

use MageOS\ShoppingFeed\Model\Logger;

class Result extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $_template = 'feed/test/result.phtml';

    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Factory
     */
    protected $generatorFactory;

    /**
     * @var Logger\Handler\MemoryStream
     */
    protected $memoryHandler;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param array                                   $data
     */
    public function __construct(
        \MageOS\ShoppingFeed\Model\Generator\Factory $generatorFactory,
        \MageOS\ShoppingFeed\Model\Logger\Handler\MemoryStream $memoryHandler,
        \MageOS\ShoppingFeed\Model\Logger $logger,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->generatorFactory = $generatorFactory;
        $this->memoryHandler = $memoryHandler;
        $this->logger = $logger;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $feed = $this->registry->registry('feed');
        $product = $this->registry->registry('current_test_product');
        $messages = [];

        if ($feed && $feed->getId() && $product && $product->getId()) {
            $messages[] = __('Starting test generation for feed #%1, SKU #%2', $feed->getName(), $product->getSku());
            try {
                /**
 * @var \MageOS\ShoppingFeed\Model\Generator $generator
*/
                $generator = $this->generatorFactory->create($feed, null, $product->getSku());
                $this->logger->resetHandler()
                    ->pushHandler($this->memoryHandler);

                $generator->run();
                $output = $generator->getTestOutput();
                $logOutput = $this->memoryHandler->getLogContent();
                $this->setTestOutput($output);
                $this->setLogOutput($logOutput);

                if (is_array($output) && count($output) == 0) {
                    $messages[] = __('No output found. Maybe product SKU #%1 is not visible/searchable or is associated product? See Log Output for more information.', $product->getSku());
                }

                $messages[] = __('Test feed was generated.');
            } catch (\Exception $e) {
                // We show any and all errors on test run
                $messages[] = $e->getMessage();
                $traceAsArray = explode("\n", $e->getTraceAsString());
                $messages = array_merge($messages, array_slice($traceAsArray, 0, 5));
            }
        }
        $this->setMessages($messages);
        return $this;
    }
}
