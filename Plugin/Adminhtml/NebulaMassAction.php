<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Plugin\Adminhtml;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Ui\Component\MassAction\Filter;

/** Translate Nebula's explicit selection only for this module's POST controllers. */
class NebulaMassAction
{
    private const ACTIONS = ['massenable', 'massdisable', 'massclone', 'massdelete'];

    public function __construct(private Http $request)
    {
    }

    public function aroundGetCollection(Filter $subject, callable $proceed, AbstractDb $collection)
    {
        $ids = $this->request->getParam('ids');
        if ($this->request->getRouteName() !== 'mageos_shopping_feed'
            || $this->request->getControllerName() !== 'feed'
            || !in_array(strtolower((string) $this->request->getActionName()), self::ACTIONS, true)
            || $ids === null
        ) {
            return $proceed($collection);
        }
        if (!$this->request->isPost() || !is_array($ids) || !$ids || count($ids) > 200) {
            throw new LocalizedException(__('Please select valid feed IDs.'));
        }
        foreach ($ids as $id) {
            if ((!is_string($id) && !is_int($id)) || !ctype_digit((string) $id)
                || (int) $id < 1 || (string) (int) $id !== (string) $id
            ) {
                throw new LocalizedException(__('Please select valid feed IDs.'));
            }
        }
        return $collection->addFieldToFilter($collection->getIdFieldName(), ['in' => array_unique($ids)]);
    }
}
