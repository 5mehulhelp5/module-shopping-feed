<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Ui\DataProvider\Feed;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use MageOS\ShoppingFeed\Model\Adminhtml\FeedFormData;
use MageOS\ShoppingFeed\Model\ResourceModel\Feed\CollectionFactory;
use MageOS\ShoppingFeed\Ui\DataProvider\Feed\Form\Metadata;
use MageOS\ShoppingFeed\Ui\DataProvider\Feed\Form\Promotions;

class FormDataProvider extends AbstractDataProvider
{
    /**
     * @var array|null
     */
    private ?array $loadedData = null;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param FeedFormData $formData
     * @param Metadata $metadata
     * @param Promotions $promotions
     * @param DataPersistorInterface $dataPersistor
     * @param PoolInterface $pool
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        private Registry $registry,
        private FeedFormData $formData,
        private Metadata $metadata,
        private Promotions $promotions,
        private DataPersistorInterface $dataPersistor,
        private PoolInterface $pool,
        private \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get the form metadata for the current feed, after the modifier pool.
     *
     * @return array
     */
    public function getMeta()
    {
        $meta = array_replace_recursive(parent::getMeta(), $this->metadata->get($this->getFeed()));
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }
        return $meta;
    }

    /**
     * Get the form data for the current feed, restoring persisted values after a failed save.
     *
     * @return array
     */
    public function getData()
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }
        $feed = $this->getFeed();
        $data = $this->formData->project($feed, $this->metadata->configKeys($feed));
        if (empty($data['config']['general_currency'])) {
            // Preserve existing output when a legacy feed has no explicit currency.
            if ($feed->getId()) {
                $data['config']['general_currency'] = $feed->getStore()->getCurrentCurrencyCode();
            } else {
                /** @var \Magento\Store\Model\Store $store */
                $store = $this->storeManager->getStore($feed->getStoreId());
                $data['config']['general_currency'] = $store->getDefaultCurrencyCode();
            }
        }
        $recovery = $this->dataPersistor->get(FeedFormData::PERSISTOR_KEY);
        if (is_array($recovery) && (string)($recovery['id'] ?? '') === (string)$feed->getId()
            && ($recovery['type'] ?? '') === $feed->getType()
            && (int)($recovery['store_id'] ?? 0) === (int)$feed->getStoreId()
        ) {
            // Replace complete config values/row collections, never recursively merge row indexes.
            $data = array_replace(
                $data,
                $recovery,
                ['config' => array_replace($data['config'], $recovery['config'] ?? [])]
            );
            $this->dataPersistor->clear(FeedFormData::PERSISTOR_KEY);
        }
        if ($feed->getType() === 'google_shopping') {
            $data = $this->promotions->data($feed, $data);
        }
        $result = [$feed->getId() ?: '' => $this->formData->redact($data)];
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $result = $modifier->modifyData($result);
        }
        return $this->loadedData = $result;
    }

    /**
     * Get the provider config with the encoded initial form data.
     *
     * @return mixed
     */
    public function getConfigData()
    {
        $config = parent::getConfigData();
        $data = $this->getData();
        $config['data'] = ['feed_form_initial_data' => $this->formData->encodeForProvider(
            $data[$this->getFeed()->getId() ?: '']
        )];
        return $config;
    }

    /**
     * Get the feed being edited, defaulting a new feed to the default store view.
     *
     * @return \MageOS\ShoppingFeed\Model\Feed
     */
    private function getFeed(): \MageOS\ShoppingFeed\Model\Feed
    {
        $feed = $this->registry->registry('feed');
        if (!$feed->getId() && !$feed->getStoreId()) {
            $feed->setStoreId($this->storeManager->getDefaultStoreView()->getId());
        }
        return $feed;
    }
}
