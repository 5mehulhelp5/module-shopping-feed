<?php
namespace MageOS\ShoppingFeed\Model\Feed\Source;

class Encoding
{
    /**
     * Returns supported encodings for mbstring/mb_convert_encoding
     *
     * @return array
     */
    public function toOptionArray()
    {
        $encodings = [
            'UTF-8',
            'ISO-8859-1',
            'Windows-1251',
            'Windows-1252',
            'ASCII',
            'ISO-8859-15',
            'CP1252',
            'CP1251',
            'KOI8-R',
            'KOI8-U',
            'JIS',
            'EUC-JP',
            'SJIS',
            'ISO-2022-JP',
            'GB2312',
            'BIG5',
            'EUC-KR',
            'ISO-2022-KR',
            'ISO-2022-CN',
            'HZ',
            'EUC-CN',
            'UTF-7',
            'UTF-16',
            'UTF-32',
        ];
        $options = [];
        foreach ($encodings as $encoding) {
            $options[] = [
                'value' => $encoding,
                'label' => $encoding
            ];
        }
        return $options;
    }
}
