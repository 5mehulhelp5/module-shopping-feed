<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Integration;

use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Generator\Factory;
use MageOS\ShoppingFeed\Setup\Patch\Data\AddSkipSubmitAttribute;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class ProductAttributeTest extends TestCase
{
    private const SKIP = 'mageos_shopping_feed_skip_submit';

    public function testSetupUpgradeCreatesTheFieldAndAssignsEveryExistingAttributeSet(): void
    {
        $om = Bootstrap::getObjectManager();
        $attribute = $om->get(Config::class)->getAttribute(Product::ENTITY, self::SKIP);
        self::assertNotEmpty($attribute->getId());
        self::assertSame('boolean', $attribute->getFrontendInput());
        self::assertSame('0', (string)$attribute->getDefaultValue());
        self::assertTrue((bool)$attribute->getIsVisible());
        self::assertTrue((bool)$attribute->getIsUserDefined(), 'Merchants must be able to manage the field.');
        self::assertSame(ScopedAttributeInterface::SCOPE_STORE, (int)$attribute->getIsGlobal());
        $db = $om->get(ResourceConnection::class)->getConnection();
        $sets = $db->fetchCol($db->select()->from($db->getTableName('eav_attribute_set'), 'attribute_set_id')
            ->where('entity_type_id = ?', $attribute->getEntityTypeId()));
        $assigned = $db->fetchPairs($db->select()
            ->from($db->getTableName('eav_entity_attribute'), ['attribute_set_id', 'sort_order'])
            ->where('attribute_id = ?', $attribute->getId()));
        self::assertEqualsCanonicalizing($sets, array_keys($assigned));
        self::assertSame([100], array_values(array_unique(array_map('intval', $assigned))));
        self::assertSame(1, (int)$db->fetchOne($db->select()->from($db->getTableName('patch_list'), 'COUNT(*)')
            ->where('patch_name = ?', AddSkipSubmitAttribute::class)));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testExistingMerchantDefinitionAndProductValueSurvivePatchApplication(): void
    {
        $om = Bootstrap::getObjectManager();
        $setup = $om->create(EavSetup::class);
        $code = self::SKIP;
        $setup->updateAttribute(Product::ENTITY, $code, 'frontend_label', 'Merchant exclusion');
        $setup->updateAttribute(Product::ENTITY, $code, 'is_global', ScopedAttributeInterface::SCOPE_GLOBAL);
        $product = $om->create(Product::class)->load(1);
        $product->setData($code, 1);
        $product->getResource()->saveAttribute($product, $code);
        $om->create(AddSkipSubmitAttribute::class)->apply();
        $om->get(Config::class)->clear();
        $attribute = $om->get(Config::class)->getAttribute(Product::ENTITY, $code);
        self::assertSame('Merchant exclusion', $attribute->getFrontendLabel());
        self::assertSame(ScopedAttributeInterface::SCOPE_GLOBAL, (int)$attribute->getIsGlobal());
        self::assertSame(1, (int)$om->create(Product::class)->load(1)->getData($code));
    }

    public function testPatchRecreatesTheFieldWhenAppliedAfterDeletion(): void
    {
        $om = Bootstrap::getObjectManager();
        $setup = $om->create(EavSetup::class);
        $setup->removeAttribute(Product::ENTITY, self::SKIP);
        $om->get(Config::class)->clear();
        self::assertEmpty($setup->getAttributeId(Product::ENTITY, self::SKIP));
        $om->create(AddSkipSubmitAttribute::class)->apply();
        self::assertNotEmpty($setup->getAttributeId(Product::ENTITY, self::SKIP));
    }

    public function testAttributeSetCreatedAfterUpgradeInheritsTheFieldInTheShoppingFeedGroup(): void
    {
        $om = Bootstrap::getObjectManager();
        $type = $om->get(Config::class)->getEntityType(Product::ENTITY);
        $set = $om->create(AttributeSet::class);
        $set->setData([
            'attribute_set_name' => 'Shopping Feed regression set',
            'entity_type_id' => (int)$type->getId(),
        ]);
        $set->validate();
        $set->save();
        $set->initFromSkeleton((int)$type->getDefaultAttributeSetId())->save();
        $attributeId = (int)$om->get(Config::class)->getAttribute(Product::ENTITY, self::SKIP)->getId();
        $db = $om->get(ResourceConnection::class)->getConnection();
        $groups = $db->fetchCol($db->select()->from(['ea' => $db->getTableName('eav_entity_attribute')], [])
            ->join(
                ['g' => $db->getTableName('eav_attribute_group')],
                'g.attribute_group_id = ea.attribute_group_id',
                ['attribute_group_name']
            )
            ->where('ea.attribute_set_id = ?', (int)$set->getId())
            ->where('ea.attribute_id = ?', $attributeId));
        self::assertSame(['Shopping Feed'], $groups);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSavedProductExclusionControlsGeneratedRows(): void
    {
        $om = Bootstrap::getObjectManager();
        $feed = $this->feed(1, ['sku' => 'sku']);
        foreach ([0, 1, 0] as $skip) {
            $product = $om->create(Product::class)->setStoreId(1)->load(1);
            $product->setData(self::SKIP, $skip);
            $product->getResource()->saveAttribute($product, self::SKIP);
            $generator = $om->get(Factory::class)->create($feed, null, 'simple');
            $generator->run();
            self::assertCount($skip ? 0 : 1, $generator->getTestOutput());
        }
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     */
    public function testStoreViewExclusionAppliesToConfigurableChildrenAndParent(): void
    {
        $om = Bootstrap::getObjectManager();
        $second = (int)$om->get(StoreManagerInterface::class)->getStore('fixture_second_store')->getId();
        $ids = [];
        foreach (['simple_10', 'simple_20', 'configurable'] as $sku) {
            $ids[$sku] = (int)$om->get(ProductRepositoryInterface::class)->get($sku)->getId();
        }
        self::assertSame(['simple_10', 'simple_20'], $this->childSkus(1));
        self::assertSame(['simple_10', 'simple_20'], $this->childSkus($second));

        // Yes only in the second store view: excluded there, kept in the default store view.
        $this->saveSkip($ids['simple_10'], $second, 1);
        self::assertSame(['simple_20'], $this->childSkus($second));
        self::assertSame(['simple_10', 'simple_20'], $this->childSkus(1));

        // Yes in All Store Views: excluded everywhere.
        $this->saveSkip($ids['simple_20'], 0, 1);
        self::assertSame(['simple_10'], $this->childSkus(1));
        self::assertSame([], $this->childSkus($second));

        // An explicit No override restores the child in that store view only.
        $this->saveSkip($ids['simple_10'], $second, 0);
        self::assertSame(['simple_10'], $this->childSkus($second));

        // Parent exclusion suppresses every child row.
        $this->saveSkip($ids['configurable'], 0, 1);
        self::assertSame([], $this->childSkus(1));
        self::assertSame([], $this->childSkus($second));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSelectBranchResolvesCoreSourcesAndDropsStaleMultiselectIds(): void
    {
        $om = Bootstrap::getObjectManager();
        $setup = $om->create(EavSetup::class);
        $setup->addAttribute(Product::ENTITY, 'shopping_feed_test_stale', [
            'type' => 'varchar', 'input' => 'multiselect', 'label' => 'shopping_feed_test_stale',
            'required' => false, 'user_defined' => true, 'group' => 'General', 'backend' => ArrayBackend::class,
            'option' => ['value' => ['a' => [0 => 'Alpha'], 'b' => [0 => 'Beta']]],
        ]);
        $om->get(Config::class)->clear();
        $attribute = $om->get(Config::class)->getAttribute(Product::ENTITY, 'shopping_feed_test_stale')->setStoreId(0);
        $byLabel = array_column($attribute->getSource()->getAllOptions(), 'value', 'label');
        $product = $om->create(Product::class)->setStoreId(0)->load(1);
        $product->setData('shopping_feed_test_stale', $byLabel['Beta'] . ',999999,' . $byLabel['Alpha']);
        $product->getResource()->saveAttribute($product, 'shopping_feed_test_stale');
        $product->setData('country_of_manufacture', 'US');
        $product->getResource()->saveAttribute($product, 'country_of_manufacture');
        $shared = $om->get(Config::class)->getAttribute(Product::ENTITY, 'country_of_manufacture');
        $storeBefore = $shared->getStoreId();

        $generator = $om->get(Factory::class)->create($this->feed(1, [
            'stale' => 'shopping_feed_test_stale', 'country' => 'country_of_manufacture', 'status' => 'status',
            'visibility' => 'visibility', 'tax' => 'tax_class_id', 'stock' => 'quantity_and_stock_status',
            'skip' => self::SKIP,
        ]), null, 'simple');
        $generator->run();
        $rows = $generator->getTestOutput();
        self::assertCount(1, $rows);
        $values = array_column(reset($rows), 'value', 'label');
        self::assertSame('Beta, Alpha', $values['stale']);
        self::assertSame('United States', $values['country']);
        self::assertSame('Enabled', $values['status']);
        self::assertSame('Catalog, Search', $values['visibility']);
        self::assertSame('Taxable Goods', $values['tax']);
        self::assertSame('In Stock', $values['stock']);
        self::assertSame('No', $values['skip']);
        self::assertSame($storeBefore, $shared->getStoreId());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     */
    public function testGeneratedSelectAndMultiselectLabelsUseEachFeedsStoreAndAdminFallback(): void
    {
        $om = Bootstrap::getObjectManager();
        $storeId = (int)$om->get(StoreManagerInterface::class)->getStore('fixture_second_store')->getId();
        $setup = $om->create(EavSetup::class);
        $product = $om->create(Product::class)->setStoreId(0)->load(1);
        foreach (['select', 'multiselect'] as $input) {
            $code = 'shopping_feed_test_' . $input;
            $setup->addAttribute(Product::ENTITY, $code, [
                'type' => 'varchar', 'input' => $input, 'label' => $code, 'required' => false,
                'user_defined' => true, 'group' => 'General',
                'backend' => $input === 'multiselect' ? ArrayBackend::class : null,
                'option' => ['value' => [
                    'translated' => [0 => 'Admin label', $storeId => '本地标签'],
                    'fallback' => [0 => 'Fallback'],
                ]],
            ]);
            $om->get(Config::class)->clear();
            $attribute = $om->get(Config::class)->getAttribute(Product::ENTITY, $code)->setStoreId(0);
            $options = array_column($attribute->getSource()->getAllOptions(), 'value', 'label');
            $product->setData($code, $input === 'select'
                ? $options['Admin label']
                : $options['Admin label'] . ',' . $options['Fallback']);
            $product->getResource()->saveAttribute($product, $code);
        }
        foreach ([$storeId, 1, $storeId] as $feedStoreId) {
            $feed = $this->feed($feedStoreId, [
                'brand' => 'shopping_feed_test_select', 'color' => 'shopping_feed_test_multiselect',
            ]);
            $generator = $om->get(Factory::class)->create($feed, null, 'simple');
            $generator->run();
            $rows = $generator->getTestOutput();
            self::assertCount(1, $rows);
            $values = array_column(reset($rows), 'value', 'label');
            $label = $feedStoreId === $storeId ? '本地标签' : 'Admin label';
            self::assertSame($label, $values['brand']);
            self::assertSame($label . ', Fallback', $values['color']);
        }
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @param int $value
     */
    private function saveSkip(int $productId, int $storeId, int $value): void
    {
        $product = Bootstrap::getObjectManager()->create(Product::class)->setStoreId($storeId)->load($productId);
        $product->setData(self::SKIP, $value);
        $product->getResource()->saveAttribute($product, self::SKIP);
    }

    /**
     * Child SKUs the configurable fixture produces for a feed in the given store, sorted.
     *
     * @param int $storeId
     * @return string[]
     */
    private function childSkus(int $storeId): array
    {
        $feed = $this->feed($storeId, ['sku' => 'sku'], [
            'configurable_associated_products_mode' => 1,
            'configurable_add_out_of_stock' => 1,
            'filters_add_out_of_stock' => 1,
        ]);
        $generator = Bootstrap::getObjectManager()->get(Factory::class)->create($feed, null, 'configurable');
        $generator->run();
        $skus = [];
        foreach ($generator->getTestOutput() as $row) {
            $skus[] = array_column($row, 'value', 'label')['sku'];
        }
        sort($skus);
        return $skus;
    }

    /**
     * @param int $storeId
     * @param array<string, string> $columns
     * @param array<string, mixed> $config
     * @return Feed
     */
    private function feed(int $storeId, array $columns, array $config = []): Feed
    {
        $feed = Bootstrap::getObjectManager()->create(Feed::class)->setType('generic')->setStoreId($storeId)
            ->setName('Product attribute regression')->setStatus(0)->setUseMicrodata(0)
            ->setSchedules([])->setUploads([]);
        $map = [];
        foreach ($columns as $column => $attribute) {
            $map[] = ['column' => $column, 'attribute' => $attribute, 'param' => '', 'order' => count($map)];
        }
        $feed->getConfig()->setData('shipping_cache_enabled', 0)
            ->setData('filters_skip_column_empty', [])->setData('categories_include_all_products', 1)
            ->setData('columns_product_columns', $map);
        foreach ($config as $key => $value) {
            $feed->getConfig()->setData($key, $value);
        }
        $feed->save();
        return $feed;
    }
}
