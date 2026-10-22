<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddSkipSubmitAttribute implements DataPatchInterface
{
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory
    ) {
    }

    /**
     * Create the product exclusion field without changing a merchant's existing attribute.
     *
     * @return $this
     */
    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();
        try {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            if (!$eavSetup->getAttributeId(Product::ENTITY, 'mageos_shopping_feed_skip_submit')) {
                $eavSetup->addAttribute(Product::ENTITY, 'mageos_shopping_feed_skip_submit', [
                    'type' => 'int',
                    'label' => 'Skip from Being Submitted',
                    'input' => 'boolean',
                    'source' => Boolean::class,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => 0,
                    'group' => 'Shopping Feed',
                    'sort_order' => 100,
                ]);
            }
        } finally {
            $connection->endSetup();
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
