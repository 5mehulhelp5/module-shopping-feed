<?php
declare(strict_types=1);

// Resolve this block's template from the module's normal and Hyva layout handles.
$root = dirname(__DIR__, 3);
$handles = ['catalog_product_view_type_simple'];
if (($argv[1] ?? 'hyva') === 'hyva') {
    $handles[] = 'hyva_catalog_product_view_type_simple';
}
$template = '';
foreach ($handles as $handle) {
    $file = $root . '/view/frontend/layout/' . $handle . '.xml';
    if (!is_file($file)) {
        continue;
    }
    $layout = simplexml_load_file($file);
    foreach ($layout->xpath('//*[@name="mageos.shopping_feed.product.autoselect"][@template]') as $node) {
        $template = (string) $node['template'];
    }
}

$hyvaCsp = new class {
    /**
     * Leave the script unchanged for isolated JavaScript tests.
     */
    public function registerInlineScript(): void
    {
        // The browser fixture supplies a CSP hash for the rendered script.
    }
};
$secureRenderer = new class {
    /**
     * Mirror SecureHtmlRenderer::renderTag() for raw script content without a store's CSP collector.
     *
     * @param string $tagName
     * @param array<string, string> $attributes
     * @param string|null $content
     * @param bool $textContent
     * @return string
     */
    public function renderTag(string $tagName, array $attributes, ?string $content = null, bool $textContent = true): string
    {
        $attributeHtml = '';
        foreach ($attributes as $name => $value) {
            $attributeHtml .= sprintf(' %s="%s"', $name, htmlspecialchars($value, ENT_QUOTES));
        }
        $content = $textContent ? htmlspecialchars((string) $content, ENT_QUOTES) : (string) $content;

        return sprintf('<%1$s%2$s>%3$s</%1$s>', $tagName, $attributeHtml, $content);
    }
};
require $root . '/view/frontend/templates/' . str_replace('MageOS_ShoppingFeed::', '', $template);
