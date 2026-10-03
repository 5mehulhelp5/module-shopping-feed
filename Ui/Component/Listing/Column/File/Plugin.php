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
        FeedFactory $feedFactory,
        private \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        private \Magento\Framework\Escaper $escaper
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
                    $filepath = $messages['promotion_file'] ?? '';
                    if (!is_string($filepath) || $filepath === '' || !isset($messages['promotion_added'])) {
                        continue;
                    }
                    $path = realpath($filepath);
                    $media = realpath($this->directoryList->getPath('media'));
                    if ($media && $path && is_file($path) && str_starts_with($path, $media . '/')) {
                        $store = $this->storeManager->getStore((int)$feed->getStoreId());
                        $url = sprintf(
                            '%s%s',
                            $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA),
                            substr($path, strlen($media) + 1)
                        );
                        $item[$name] .= '<br /><a href="' . $this->escaper->escapeUrl($url)
                            . '" target="_blank" rel="noopener">' . $this->escaper->escapeHtml($url) . '</a><br />'
                            . $this->escaper->escapeHtml((string) __('%1 promotion rows', (int) $messages['promotion_added']));
                    }
                }
            }
        }

        return $dataSource;
    }
}
