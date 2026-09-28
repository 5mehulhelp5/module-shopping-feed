<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Plugin\Adminhtml;

/** Use the complete Magento editor on feed detail pages; Nebula keeps its native listing. */
class FeedEditorTheme
{
    public function __construct(
        private \Magento\Framework\App\Request\Http $request,
        private \Magento\Framework\Module\Manager $modules
    ) {
    }

    public function isFeedEditor(): bool
    {
        return $this->modules->isEnabled('Qoliber_Nebula')
            && $this->request->getRouteName() === 'mageos_shopping_feed'
            && $this->request->getControllerName() === 'feed'
            && in_array(strtolower((string) $this->request->getActionName()), ['new', 'edit', 'test', 'viewlog'], true);
    }

    public function beforeSetDesignTheme($subject, $themeId = null, $area = null): array
    {
        if (($area === 'adminhtml' || ($area === null && $subject->getArea() === 'adminhtml'))
            && $this->isFeedEditor()
        ) {
            $themeId = 'Magento/backend';
        }
        return [$themeId, $area];
    }

    public function afterGetPageLayout($subject, ?string $result): ?string
    {
        if ($this->isFeedEditor()) {
            return ['admin-1column-top' => 'admin-1column', 'admin-2columns-left-top' => 'admin-2columns-left'][$result]
                ?? $result;
        }
        return $result;
    }

    public function afterLoad($subject, $result)
    {
        if ($this->isFeedEditor()) {
            $subject->addUpdate('<referenceContainer name="page.menu">'
                . '<block class="MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Menu"'
                . ' name="menu" as="menu" template="Magento_Backend::menu.phtml"/>'
                . '</referenceContainer>');
            // Nebula's global assets belong to its theme and are not present in Magento/backend.
            foreach (['nebula.vendor.sortable', 'nebula.component.scripts', 'nebula.directive.scripts',
                'nebula.form.scripts', 'nebula.grid.scripts', 'nebula.media.scripts', 'nebula.quill.scripts'] as $name) {
                $subject->addUpdate('<referenceBlock name="' . $name . '" remove="true"/>');
            }
        }
        return $result;
    }
}
