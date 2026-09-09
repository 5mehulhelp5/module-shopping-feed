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
require $root . '/view/frontend/templates/' . str_replace('MageOS_ShoppingFeed::', '', $template);
