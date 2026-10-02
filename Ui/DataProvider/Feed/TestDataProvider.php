<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Ui\DataProvider\Feed;

class TestDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \MageOS\ShoppingFeed\Model\ResourceModel\Feed\CollectionFactory $collectionFactory,
        private \Magento\Framework\Registry $registry,
        private \Magento\Framework\App\RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $id = $this->registry->registry('feed')->getId();
        $sku = $this->request->getParam('sku', '');
        return [$id => ['id' => $id, 'sku' => is_string($sku) || is_int($sku) ? (string)$sku : '',
            'type' => $this->request->getParam('type') === 'id' ? 'id' : 'sku']];
    }
}
