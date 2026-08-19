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

namespace MageOS\ShoppingFeed\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use MageOS\ShoppingFeed\Model\FeedFactory;

/**
 * Class File
 */
class File extends Column
{
    /**
     * Feed model factory
     *
     * @var \MageOS\ShoppingFeed\Model\FeedFactory
     */
    protected $feedFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * Constructor
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FeedFactory        $feedFactory
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        FeedFactory $feedFactory,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        array $components = [],
        array $data = []
    ) {
        $this->feedFactory = $feedFactory;
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $mageRootPath = $this->directoryList->getRoot();

            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['id'])) {
                    $feed = $this->feedFactory->create()->setData($item);
                    $fileInformation = $feed->getMessages();
                    $filepath = isset($fileInformation['file']) ? $fileInformation['file'] : '';
                    $absfilepath = rtrim($mageRootPath) . '/'. ltrim($filepath, '/');

                    if (file_exists($absfilepath) && isset($fileInformation['skipped'])) {
                        /**
 * @var \Magento\Store\Model\Store $store
*/
                        $store = $this->storeManager->getStore((int)$fileInformation['store_id']);
                        $pathHasPub = strpos($_SERVER['DOCUMENT_ROOT'], '/pub');
                        $path = $pathHasPub > 0 ? str_replace('pub/', '', $filepath) : $filepath;
                        $url = sprintf(
                            '%s%s',
                            $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),
                            $path
                        );
                        $item[$name] = '<a href="' . $url . '" target="_blank">' . $url . '</a><br />'
                            . __(
                                '%4 - processed %1 products, added %2 rows, %3 rows skipped',
                                $fileInformation['added'],
                                $fileInformation['exported'],
                                $fileInformation['skipped'],
                                $fileInformation['date']
                            );
                    } elseif (file_exists($absfilepath . '.tmp') && isset($fileInformation['skipped'])) {
                        $item[$name] = __('Feed file not ready.'). '<br />'. __(
                            '%4 - processed %1 products, added %2 rows, %3 rows skipped',
                            $fileInformation['added'],
                            $fileInformation['exported'],
                            $fileInformation['skipped'],
                            $fileInformation['date']
                        );
                    } else {
                        $item[$name] = 'Feed file not ready.';
                    }
                }
            }
        }

        return $dataSource;
    }
}
