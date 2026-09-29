<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Plugin\Adminhtml;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Layout\ProcessorInterface;

/** Optional integration: no Nebula PHP classes are required by this module. */
class NebulaLayout
{
    private ?\WeakMap $injected = null;

    public function __construct(private Manager $modules, private DesignInterface $design, private Http $request)
    {
    }

    public function afterLoad(ProcessorInterface $subject, ProcessorInterface $result): ProcessorInterface
    {
        if (!$this->modules->isEnabled('Qoliber_NebulaGrid')
            || $this->design->getDesignTheme()->getCode() !== 'Qoliber/Nebula'
            || $this->request->getFullActionName() !== 'mageos_shopping_feed_feed_index'
        ) {
            return $result;
        }
        $this->injected ??= new \WeakMap();
        if (!isset($this->injected[$subject])) {
            $subject->addUpdate('<referenceBlock name="feed_list" remove="true"/>'
                . '<referenceBlock name="mageos_shopping_feed_grid" remove="true"/>'
                . '<referenceContainer name="content">'
                . '<block class="MageOS\ShoppingFeed\Block\Adminhtml\Nebula\Toolbar"'
                . ' name="shopping.feed.nebula.toolbar" before="-"'
                . ' template="MageOS_ShoppingFeed::nebula/toolbar.phtml"/>'
                . '<block class="Qoliber\NebulaGrid\Block\Grid" name="shopping.feed.nebula.grid">'
                . '<arguments><argument name="grid_id" xsi:type="string">mageos_shopping_feed_grid</argument>'
                . '</arguments></block></referenceContainer>');
            $this->injected[$subject] = true;
        }
        return $result;
    }
}
