<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Plugin\Adminhtml;

/** Exports must include all matched feeds rather than the provider's default first page. */
class NebulaExport
{
    public function __construct(private \Magento\Framework\App\Request\Http $request)
    {
    }

    public function beforeGetData($subject, array $config, array $params = []): array
    {
        if (($config['collection'] ?? '') === 'shopping_feed.collection'
            && $this->request->getFullActionName() === 'nebula_grid_export'
        ) {
            $params['pageSize'] = 0;
        }
        return [$config, $params];
    }
}
