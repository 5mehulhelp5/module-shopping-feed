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

namespace MageOS\ShoppingFeed\Controller\Adminhtml\Feed;

class Test extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'MageOS_ShoppingFeed::grid';

    /**
     * @var \MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Builder
     */
    protected $feedBuilder;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $resultForwardFactory;

    /**
     * \@var Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Test constructor.
     *
     * @param \Magento\Backend\App\Action\Context               $context
     * @param Builder                                           $feedBuilder
     * @param \Magento\Framework\View\Result\PageFactory        $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Catalog\Model\ProductFactory             $productFactory
     * @param \Magento\Framework\Registry                       $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Builder $feedBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->feedBuilder = $feedBuilder;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->productFactory = $productFactory;
        $this->registry = $registry;

        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        return $this->resultPageFactory->create();
    }

    /**
     * Create new feed page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $feed = $this->feedBuilder->build($this->getRequest()->getParams());

        if (!$feed->getId()) {
            $this->messageManager->addError(__('Feed wasn\'t found'));
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('index');
        }

        $sku = $this->getRequest()->getParam('sku');
        $type = $this->getRequest()->getParam('type');

        if ($sku) {
            $product = $this->productFactory->create();

            switch ($type) {
            case 'id':
                $product->load($sku);
                break;
            case 'sku':
            default:
                $product->load($product->getIdBySku($sku));
                break;
            }

            if (!$product->getId()) {
                $this->messageManager->addError(
                    __('Could not find any product with this %1.', ucfirst($type))
                );
            } else {
                $this->registry->register('current_test_product', $product);
            }
        }

        /**
 * @var \Magento\Backend\Model\View\Result\Page $resultPage
*/
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(sprintf(__('Test Feed #%s [%s]'), $feed->getId(), $feed->getName()));

        return $resultPage;
    }
}
