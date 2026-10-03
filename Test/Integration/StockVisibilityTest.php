<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Generator\Factory;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class StockVisibilityTest extends TestCase
{
    /**
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 0
     */
    public function testGroupedOutOfStockChildIsAvailableToTheFeed(): void
    {
        $om = Bootstrap::getObjectManager();
        $product = $om->get(ProductRepositoryInterface::class)->get('simple');
        $registry = $om->get(StockRegistryInterface::class);
        $item = $registry->getStockItem((int) $product->getId());
        $item->setQty(0)->setIsInStock(false);
        $registry->updateStockItemBySku($product->getSku(), $item);
        $db = $om->get(ResourceConnection::class)->getConnection();
        $db->update($db->getTableName('cataloginventory_stock_status'), ['qty' => 0, 'stock_status' => 0],
            ['product_id = ?' => (int) $product->getId()]);

        $feed = $om->create(Feed::class)->setType('generic')->setStoreId(1)
            ->setName('Grouped stock visibility regression')->setStatus(0)->setUseMicrodata(0)
            ->setSchedules([])->setUploads([]);
        $feed->getConfig()->setData('shipping_cache_enabled', 0)
            ->setData('grouped_associated_products_mode', 1)
            ->setData('grouped_add_out_of_stock', 1)
            ->setData('configurable_add_out_of_stock', 1)
            ->setData('filters_add_out_of_stock', 1)
            ->setData('filters_skip_column_empty', [])
            ->setData('columns_product_columns', [
                ['column' => 'sku', 'attribute' => 'sku', 'param' => '', 'order' => 0],
            ]);
        $feed->save();
        $generator = $om->get(Factory::class)->create($feed, null, 'grouped-product');
        $generator->run();
        $skus = [];
        foreach ($generator->getTestOutput() as $row) {
            $skus[] = array_column($row, 'value', 'label')['sku'];
        }
        sort($skus);
        $this->assertSame(['simple', 'virtual-product'], $skus);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 0
     */
    public function testFeedControlsOutOfStockChildrenIndependentlyOfStorefrontVisibility(): void
    {
        $om = Bootstrap::getObjectManager();
        $product = $om->get(ProductRepositoryInterface::class)->get('simple_20');
        $registry = $om->get(StockRegistryInterface::class);
        $item = $registry->getStockItem((int) $product->getId());
        $item->setQty(0)->setIsInStock(false);
        $registry->updateStockItemBySku($product->getSku(), $item);

        // Set the default-stock index explicitly so the fixture does not depend on an async indexer.
        $db = $om->get(ResourceConnection::class)->getConnection();
        $db->update($db->getTableName('cataloginventory_stock_status'), ['qty' => 0, 'stock_status' => 0],
            ['product_id = ?' => (int) $product->getId()]);

        foreach ([1 => ['simple_10', 'simple_20'], 0 => ['simple_10']] as $allow => $expected) {
            $feed = $om->create(Feed::class)->setType('generic')->setStoreId(1)
                ->setName('Stock visibility regression')->setStatus(0)->setUseMicrodata(0)
                ->setSchedules([])->setUploads([]);
            $feed->getConfig()->setData('shipping_cache_enabled', 0)
                ->setData('configurable_associated_products_mode', 1)
                ->setData('configurable_add_out_of_stock', $allow)
                ->setData('filters_skip_column_empty', [])
                ->setData('columns_product_columns', [
                    ['column' => 'sku', 'attribute' => 'sku', 'param' => '', 'order' => 0],
                    ['column' => 'availability', 'attribute' => 'directive_availability',
                        'param' => '', 'order' => 1],
                ]);
            $feed->save();
            $generator = $om->get(Factory::class)->create($feed, null, 'configurable');
            $generator->run();
            $rows = [];
            foreach ($generator->getTestOutput() as $row) {
                $values = array_column($row, 'value', 'label');
                $rows[$values['sku']] = $values['availability'];
            }
            ksort($rows);
            $this->assertSame($expected, array_keys($rows));
            $this->assertSame('in_stock', $rows['simple_10']);
            if ($allow) {
                $this->assertSame('out_of_stock', $rows['simple_20']);
            }
        }

        $product->setVisibility(4);
        $product->getResource()->saveAttribute($product, 'visibility');
        foreach ([1 => ['simple_20'], 0 => []] as $allow => $expected) {
            $feed->getConfig()->setData('filters_add_out_of_stock', $allow);
            $collection = $om->create(\MageOS\ShoppingFeed\Model\Product\CollectionProvider::class)
                ->setTestSku('simple_20')->getCollection($feed);
            $this->assertSame($expected, array_values($collection->getColumnValues('sku')));
        }
    }
}
