<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Model\Adminhtml;

use MageOS\ShoppingFeed\Model\Feed;
use Magento\Framework\Escaper;

class FeedRow
{
    /**
     * @param Escaper $escaper
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Store\Model\StoreManagerInterface $stores
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directories
     */
    public function __construct(
        private Escaper $escaper,
        private \Magento\Backend\Model\UrlInterface $url,
        private \Magento\Framework\AuthorizationInterface $authorization,
        private \Magento\Framework\Data\Form\FormKey $formKey,
        private \Magento\Store\Model\StoreManagerInterface $stores,
        private \Magento\Framework\App\Filesystem\DirectoryList $directories
    ) {
    }

    /**
     * Build the feed grid row with schedule summary, file link and action links.
     *
     * @param Feed $feed
     * @return array
     */
    public function format(Feed $feed): array
    {
        $row = array_intersect_key($feed->getData(), array_flip([
            'id', 'name', 'store_id', 'type', 'status', 'created_at', 'updated_at'
        ]));
        $row['schedules'] = implode(', ', $feed->getFormattedSchedules());
        $row['file'] = $this->file($feed);
        $row['actions'] = $this->actions((int) $feed->getId());
        return $row;
    }

    /**
     * Link to the generated feed file when it exists inside pub/, otherwise a not-ready notice.
     *
     * @param Feed $feed
     * @return string
     */
    private function file(Feed $feed): string
    {
        $messages = $feed->getMessages();
        $relative = (string) ($messages['file'] ?? '');
        // phpcs:ignore Magento2.Functions.DiscouragedFunction -- realpath() containment check; a driver needs a constructor change
        $public = realpath($this->directories->getPath('pub'));
        // phpcs:ignore Magento2.Functions.DiscouragedFunction -- realpath() containment check; a driver needs a constructor change
        $path = $relative === '' ? false : realpath($this->directories->getRoot() . '/' . ltrim($relative, '/'));
        // phpcs:ignore Magento2.Functions.DiscouragedFunction -- is_file() existence check; a driver needs a constructor change
        if (!$public || !$path || !is_file($path) || !str_starts_with($path, $public . '/')) {
            return $this->escaper->escapeHtml((string) __('Feed file not ready.'));
        }
        $store = $this->stores->getStore((int) $feed->getStoreId());
        $url = $store->getBaseUrl() . substr($path, strlen($public) + 1);
        return '<a target="_blank" rel="noopener" href="' . $this->escaper->escapeUrl($url) . '">'
            // phpcs:ignore Magento2.Functions.DiscouragedFunction -- link text from a resolved local path; no driver injected
            . $this->escaper->escapeHtml(basename($path)) . '</a><br>'
            . $this->escaper->escapeHtml((string) __(
                '%1 rows; updated %2',
                $messages['exported'] ?? 0,
                $messages['date'] ?? ''
            ));
    }

    /**
     * Configure, test, log and run-now controls the current admin user may use.
     *
     * @param int $id
     * @return string
     */
    private function actions(int $id): string
    {
        $html = '';
        foreach (['edit' => [__('Configure'), 'save'], 'test' => [__('Test Feed'), 'grid'],
            'viewlog' => [__('View Log'), 'grid']] as $action => [$label, $permission]) {
            if ($this->authorization->isAllowed('MageOS_ShoppingFeed::' . $permission)) {
                $html .= '<a href="' . $this->escaper->escapeUrl($this->url->getUrl(
                    'mageos_shopping_feed/feed/' . $action,
                    ['id' => $id]
                )) . '">' . $this->escaper->escapeHtml((string) $label) . '</a> ';
            }
        }
        if ($this->authorization->isAllowed('MageOS_ShoppingFeed::generate')) {
            $html .= '<form method="post" action="' . $this->escaper->escapeUrl($this->url->getUrl(
                'mageos_shopping_feed/feed/generate',
                ['id' => $id]
            )) . '"><input type="hidden" name="form_key" value="'
                . $this->escaper->escapeHtmlAttr($this->formKey->getFormKey())
                . '"><button type="submit">' . $this->escaper->escapeHtml((string) __('Run Now')) . '</button></form>';
        }
        return $html;
    }
}
