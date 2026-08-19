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

namespace MageOS\ShoppingFeed\Ui\Component\Listing\Column\File;

use Magento\Store\Model\StoreManagerInterface;
use MageOS\ShoppingFeed\Model\FeedFactory;

class Plugin
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FeedFactory
     */
    protected $feedFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        FeedFactory $feedFactory
    ) {

        $this->feedFactory = $feedFactory;
        $this->storeManager = $storeManager;
    }

    public function afterPrepareDataSource($subject, $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $subject->getData('name');

                if (isset($item['id'])) {
                    $feed = $this->feedFactory->create()->setData($item);
                    $messages = $feed->getMessages();
                    $filepath = isset($messages['promotion_file']) ? $messages['promotion_file'] : '';

                    if (file_exists($filepath) && isset($messages['promotion_added'])) {
                        $store = $this->storeManager->getStore((int)$feed->getStoreId());
                        $url = sprintf(
                            '%s%s',
                            $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),
                            ltrim(str_replace(BP, '', $filepath), '/')
                        );
                        $item[$name] .= '<br /><a href="' . $url . '" target="_blank">' . $url . '</a><br />'
                            . __('%1 promotion rows', $messages['promotion_added']);
                    }
                }
            }
        }

        return $dataSource;
    }
}
