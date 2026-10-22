<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Integration;

use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Generator\Factory;
use MageOS\ShoppingFeed\Setup\Patch\Data\AddSkipSubmitAttribute;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
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
    public function testSetupUpgradeCreatesTheFieldAndAssignsEveryExistingAttributeSet(): void
    {
        $om = Bootstrap::getObjectManager();
        $attribute = $om->get(Config::class)->getAttribute(Product::ENTITY, 'mageos_shopping_feed_skip_submit');
        self::assertNotEmpty($attribute->getId());
        self::assertSame('boolean', $attribute->getFrontendInput());
        self::assertSame('0', (string)$attribute->getDefaultValue());
        self::assertTrue((bool)$attribute->getIsVisible());
        self::assertSame(ScopedAttributeInterface::SCOPE_STORE, (int)$attribute->getIsGlobal());
        $db = $om->get(ResourceConnection::class)->getConnection();
        $sets = $db->fetchCol($db->select()->from($db->getTableName('eav_attribute_set'), 'attribute_set_id')
            ->where('entity_type_id = ?', $attribute->getEntityTypeId()));
        $assigned = $db->fetchCol($db->select()->from($db->getTableName('eav_entity_attribute'), 'attribute_set_id')
            ->where('attribute_id = ?', $attribute->getId()));
        self::assertEqualsCanonicalizing($sets, $assigned);
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
        $code = 'mageos_shopping_feed_skip_submit';
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

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSavedProductExclusionControlsGeneratedRows(): void
    {
        $om = Bootstrap::getObjectManager();
        $feed = $this->feed(1, ['sku' => 'sku']);
        foreach ([0, 1, 0] as $skip) {
            $product = $om->create(Product::class)->setStoreId(1)->load(1);
            $product->setData('mageos_shopping_feed_skip_submit', $skip);
            $product->getResource()->saveAttribute($product, 'mageos_shopping_feed_skip_submit');
            $generator = $om->get(Factory::class)->create($feed, null, 'simple');
            $generator->run();
            self::assertCount($skip ? 0 : 1, $generator->getTestOutput());
        }
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
     * @param int $storeId
     * @param array<string, string> $columns
     * @return Feed
     */
    private function feed(int $storeId, array $columns): Feed
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
        $feed->save();
        return $feed;
    }
}
