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

// @codingStandardsIgnoreFile

namespace MageOS\ShoppingFeed\Test\Unit\Model\Feed\Source\Directory;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class AvailableCurrenciesTest extends ModelFramework
{
    /** @var \MageOS\ShoppingFeed\Model\Feed\Source\Directory\AvailableCurrencies */
    protected $sourceAvailableCurrencies;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->getModelMock('\Magento\Store\Model\Store');

        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $this->getModelMock('\Magento\Store\Model\StoreManagerInterface');

        /** @var \Magento\Directory\Model\CurrencyFactory $currencyFactory */
        $currencyFactory = $this->getModelMock('\Magento\Directory\Model\CurrencyFactory', ['create']);

        /** @var \Magento\Framework\Locale\Bundle\CurrencyBundle $currencyBundle */
        $currencyBundle = $this->getModelMock('\Magento\Framework\Locale\Bundle\CurrencyBundle');

        /** @var \Magento\Directory\Model\Currency $currency */
        $currency = $this->getModelMock('\Magento\Directory\Model\Currency');

        /** @var \Magento\Framework\Locale\Resolver $localeResolver */
        $localeResolver = $this->getModelMock('\Magento\Framework\Locale\Resolver');

        $store->expects($this->once())
            ->method('getAvailableCurrencyCodes')
            ->will($this->returnValue(['USD', 'PLN']));

        $storeManager->expects($this->exactly(2))
            ->method('getStore')
            ->will($this->returnValue($store));

        $currency->expects($this->once())
            ->method('getCurrencyRates')
            ->will($this->returnValue(['USD' => 1.0, 'PLN' => 4.0]));

        $currencyBundle->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValue([
                'Currencies' => [
                    'USD' => [1 => 'US Dollar'],
                    'PLN' => [1 => 'Polish Zloty']
                ]]
            ));

        $currencyFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($currency));

        $this->sourceAvailableCurrencies = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Feed\Source\Directory\AvailableCurrencies',
            [
                'storeManager' => $storeManager,
                'currencyFactory' => $currencyFactory,
                'currencyBundle' => $currencyBundle,
                'localeResolver' => $localeResolver
            ]
        );
    }

    public function testToOptionArray()
    {
        $this->assertEquals([
            ['value' => 'USD', 'label' => 'US Dollar'],
            ['value' => 'PLN', 'label' => 'Polish Zloty'],
        ], $this->sourceAvailableCurrencies->toOptionArray());
    }

    public function testToOptionArrayWithOptionsAlreadySet()
    {
        $this->sourceAvailableCurrencies->toOptionArray();
        $this->assertEquals([
            ['value' => 'USD', 'label' => 'US Dollar'],
            ['value' => 'PLN', 'label' => 'Polish Zloty'],
        ], $this->sourceAvailableCurrencies->toOptionArray());
    }
}
