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

namespace MageOS\ShoppingFeed\Model\Feed;

class Converter
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Builder
     */
    protected $feedBuilder;

    /**
     * @var \Magento\Framework\Json\Decoder
     */
    protected $jsonDecoder;

    /**
     * @param \Magento\Framework\App\RequestInterface                    $request
     * @param \MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Builder $feedBuilder
     * @param \Magento\Framework\Json\DecoderInterface                   $jsonDecoder
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Builder $feedBuilder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->request = $request;
        $this->feedBuilder = $feedBuilder;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * Convert an array to a feed data object for form save purposes
     *
     * @param  \MageOS\ShoppingFeed\Model\Feed $feed
     * @return \MageOS\ShoppingFeed\Model\Feed
     */
    public function populateFeedData($formData)
    {
        $feed = $this->feedBuilder->build($formData);
        $feed->setHasDataChanges(true);

        if (isset($formData['name'])) {
            $feed->setData('name', $formData['name']);
        }
        if (isset($formData['store_id'])) {
            $feed->setData('store_id', $formData['store_id']);
        }
        if (isset($formData['use_microdata'])) {
            $feed->setData('use_microdata', $formData['use_microdata']);
        }

        $config = array_key_exists('config', $formData) ? $formData['config'] : false;
        if ($config && is_array($config)) {
            foreach ($config as $path => $value) {
                $this->_configDeleteKeys($value);

                if ($path === 'categories_provider_taxonomy_by_category') {
                    if ($value != '') {
                        $value = $this->jsonDecoder->decode($value);
                    }
                    $value = $this->_configTaxonomyDeleteDefaults($value);
                }

                $feed->getConfig()->setData($path, $value);
            }
        }

        if (isset($formData['schedules']) && is_array($formData['schedules']) && !$feed->hasData('schedules')) {
            foreach ($formData['schedules'] as $key => $schedule) {
                if (!$schedule['id'] && $schedule['delete']) {
                    unset($formData['schedules'][$key]);
                }
            }
            $feed->setData('schedules', $formData['schedules']);
        }

        if (isset($formData['uploads']) && is_array($formData['uploads']) && !$feed->hasData('uploads')) {
            foreach ($formData['uploads'] as $key => $upload) {
                if (!$upload['id'] && $upload['delete']) {
                    unset($formData['uploads'][$key]);
                }
            }
            $feed->setData('uploads', $formData['uploads']);
        }

        return $feed;
    }

    /**
     * Remove config values based on delete flags
     *
     * @param  $data
     * @return mixed
     */
    protected function _configDeleteKeys(&$data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $row) {
                if (!is_array($row)) {
                    continue;
                }
                if (!empty($row['delete'])) {
                    unset($data[$key]);
                }
                if (isset($row['delete'])) {
                    unset($data[$key]['delete']);
                }
            }
        }

        return $data;
    }

    /**
     * Remove default values, only keep ones that have been configured
     *
     * @param  $data
     * @return mixed
     */
    protected function _configTaxonomyDeleteDefaults($data)
    {
        if (is_array($data)) {
            foreach ($data as $k => $row) {
                if (empty($row['tx']) && empty($row['ty'])
                    && $row['d'] == 1 && $row['p'] == 0
                ) {
                    unset($data[$k]);
                }
            }
        }

        return $data;
    }

    /**
     * Prepare array from object to fill edit form.
     *
     * @param  \MageOS\ShoppingFeed\Model\Feed $feed
     * @return mixed
     */
    public function createArrayFromObject(
        \MageOS\ShoppingFeed\Model\Feed $feed
    ) {
        $feedFormData = $feed->getData();

        if (isset($feedFormData['config']) && ($feedFormData['config'] instanceof \Magento\Framework\DataObject)) {
            foreach ($feedFormData['config']->getData() as $path => $value) {
                $feedFormData['config_' . $path] = $value;
            }
            unset($feedFormData['config']);
        }

        $feedFormData['schedules'] = $feed->getSchedules();
        $feedFormData['uploads'] = $feed->getUploads();

        return $feedFormData;
    }
}
