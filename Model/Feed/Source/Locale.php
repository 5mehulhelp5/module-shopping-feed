<?php
/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   MageOS_ShoppingFeed
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */

namespace MageOS\ShoppingFeed\Model\Feed\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Locale
 */
class Locale implements OptionSourceInterface
{
    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            'en-US',
            'cs-CZ',
            'de-DE',
            'de-CH',
            'da-DK',
            'en-AU',
            'en-CA',
            'en-GB',
            'es-ES',
            'fr-FR',
            'it-IT',
            'ja-JP',
            'nl-NL',
            'pl-PL',
            'pt-BR',
            'ru-RU',
            'sv-SE',
            'no-NO',
            'tr-TR',
        ];
    }

    /**
     * Get options as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->getOptionArray() as $value) {
            $result[] = ['value' => $value, 'label' => $value];
        }

        return $result;
    }
}
