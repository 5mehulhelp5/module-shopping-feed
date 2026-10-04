<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed;

use Magento\Backend\Block\Template;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\Manager;

class MigrationNotice extends Template
{
    /**
     * Construct the read-only migration discovery notice.
     *
     * @param Template\Context $context
     * @param ResourceConnection $resource
     * @param ComponentRegistrar $registrar
     * @param Manager $modules
     * @param AuthorizationInterface $authorization
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        private ResourceConnection $resource,
        private ComponentRegistrar $registrar,
        private Manager $modules,
        private AuthorizationInterface $authorization,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Detect installed tools or legacy data without loading legacy PHP.
     */
    public function getNoticeState(): string
    {
        if ($this->modules->isEnabled('RocketWeb_ShoppingFeedMigration')) {
            return $this->authorization->isAllowed('RocketWeb_ShoppingFeedMigration::migration')
                ? 'installed' : '';
        }
        if ($this->registrar->getPath(ComponentRegistrar::MODULE, 'RocketWeb_ShoppingFeeds')) {
            return 'legacy_module';
        }
        return $this->resource->getConnection()->isTableExists(
            $this->resource->getTableName('rw_shoppingfeeds_feed')
        ) ? 'legacy_tables' : '';
    }

    /**
     * Return the separately installed migration tool's Admin route.
     */
    public function getMigrationUrl(): string
    {
        return $this->getUrl('rocketweb_feed_migration/manage/index');
    }

    /**
     * Return installation guidance for stores that need the optional tool.
     */
    public function getInstructionsUrl(): string
    {
        return 'https://github.com/rocketweb/module-shopping-feed-migration-rocketweb#installation';
    }
}
