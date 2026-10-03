<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Test\Unit\Block\Adminhtml;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ScriptDataTest extends TestCase
{
    /** @dataProvider templates */
    #[DataProvider('templates')]
    public function testSavedDataCannotCreateMarkupInsideScript(string $template): void
    {
        $payload = "</script><script>alert(1)</script><!-- & \\" . "'\"\n";
        $html = $this->render($template, $payload);
        preg_match_all('#<script\b[^>]*>(.*?)</script\s*>#is', $html, $scripts);
        $baseline = $this->render($template, 'plain');
        self::assertSame(substr_count($baseline, '<script>'), count($scripts[0]));
        foreach ($scripts[1] as $script) {
            self::assertStringNotContainsString('<script>', $script);
            self::assertStringNotContainsString('<!--', $script);
        }
        if ($template === 'filters/find-replace') {
            preg_match("/findReplaceControl\\.addItem\\(\\s*'([^']*)'/", $html, $argument);
            $json = preg_replace('/\\\\x([0-9a-f]{2})/i', '\\u00$1', $argument[1]);
            $decoded = json_decode('"' . $json . '"', true, 512, JSON_THROW_ON_ERROR);
            self::assertSame($payload, $decoded, 'Escaping must preserve literal find/replace text.');
        }
    }

    public static function templates(): array
    {
        return [['filters/find-replace'], ['columns/columns-map'], ['categories/category-taxonomy']];
    }

    private function render(string $template, string $payload): string
    {
        $block = new class($payload) {
            public function __construct(private string $payload) {}
            public function getElement() {return new \Magento\Framework\DataObject(['html_id'=>'fixture','class'=>'fixture','name'=>'fixture','note'=>'']);}
            public function getValues() {return [['find'=>$this->payload,'replace'=>$this->payload,'column'=>'title','attribute'=>'name','order'=>1,'param'=>[$this->payload]]];}
            public function getSelectorOptions() {return ['source'=>[['label'=>$this->payload,'id'=>1]]];}
            public function isTaxonomyAutocompleteEnabled() {return true;}
            public function getControlName() {return 'columns';}
            public function getMapExistingColumns() {return false;}
            public function configToJson() {return '{}';}
            public function __call($name, $arguments) {
                if (in_array($name, ['getColumns','getDirectivesAndAttributes','getCategories'], true)) {return [];}
                if ($name === 'escapeQuote') {return str_replace('"', '&quot;', (string)$arguments[0]);}
                if (str_starts_with($name, 'escape')) {return (new \Magento\Framework\Escaper())->$name(...$arguments);}
                return '';
            }
            public function helper($name) {return new class {public function jsonEncode($value) {return json_encode($value);}};}
            public function render($path) {
                $block = $this;
                $escaper = new \Magento\Framework\Escaper();
                ob_start();
                try {include $path; return ob_get_contents();} finally {ob_end_clean();}
            }
        };
        return $block->render(dirname(__DIR__, 4) . '/view/adminhtml/templates/feed/edit/tab/' . $template . '.phtml');
    }
}
