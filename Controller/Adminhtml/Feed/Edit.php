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

class Edit extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'MageOS_ShoppingFeed::save';

    /**
     * @var \MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Builder
     */
    protected $feedBuilder;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Backend\App\Action\Context                        $context
     * @param \MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Builder $feedBuilder
     * @param \Magento\Framework\View\Result\PageFactory                 $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Builder $feedBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->feedBuilder = $feedBuilder;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageOS_ShoppingFeed::save');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        /**
 * @var \Magento\Backend\Model\View\Result\Page $resultPage
*/
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageOS_ShoppingFeed::mageos_shopping_feed')
            ->addBreadcrumb(__('Feeds Management'), __('Feeds Management'));
        return $resultPage;
    }

    /**
     * Create new feed page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $feed = $this->feedBuilder->build($this->getRequest()->getParams());

        $this->_eventManager->dispatch('mageos_shopping_feed_feed_new_action', ['feed' => $feed]);

        /**
 * @var \Magento\Backend\Model\View\Result\Page $resultPage
*/
        $resultPage = $this->resultPageFactory->create();

        $resultPage->addHandle(['mageos_shopping_feed_feed_' . $feed->getType()]);
        $resultPage->setActiveMenu('MageOS_ShoppingFeed::mageos_shopping_feed');

        $resultPage->getConfig()->getTitle()->prepend(__('Feeds Management'));
        $resultPage->getConfig()->getTitle()
            ->prepend($feed->getId() ? sprintf('%s (%s)', $feed->getName(), $feed->getType()) : __('New Feed'));

        return $resultPage;
    }
}
