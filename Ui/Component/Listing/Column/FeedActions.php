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
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class FeedActions
 */
class FeedActions extends Column
{
    /**
 * Url path
*/
    const FEED_URL_PATH_TEST = 'mageos_shopping_feed/feed/test';
    const FEED_URL_PATH_GENERATE = 'mageos_shopping_feed/feed/generate';
    const FEED_URL_PATH_VIEWLOG = 'mageos_shopping_feed/feed/viewlog';
    const FEED_URL_PATH_EDIT = 'mageos_shopping_feed/feed/edit';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
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
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['id'])) {
                    $item[$name]['generate'] = [
                        'href' => $this->urlBuilder->getUrl(self::FEED_URL_PATH_GENERATE, ['id' => $item['id']]),
                        'label' => __('Run Now'),
                        'confirm' => [
                            'title' => __('Run Feed Now'),
                            'message' => __('Add this feed to the generation queue now?'),
                        ],
                        'post' => true,
                    ];
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::FEED_URL_PATH_EDIT, ['id' => $item['id']]),
                        'label' => __('Configure'),
                    ];
                    $item[$name]['test'] = [
                        'href' => $this->urlBuilder->getUrl(self::FEED_URL_PATH_TEST, ['id' => $item['id']]),
                        'label' => __('Test Feed'),
                        'target' => '_blank',
                    ];
                    $item[$name]['viewlog'] = [
                        'href' => $this->urlBuilder->getUrl(self::FEED_URL_PATH_VIEWLOG, ['id' => $item['id']]),
                        'label' => __('View Log'),
                        'target' => '_blank',
                    ];
                }
            }
        }

        return $dataSource;
    }
}
