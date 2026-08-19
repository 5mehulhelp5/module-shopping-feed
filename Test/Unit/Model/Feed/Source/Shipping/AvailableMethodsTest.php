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

namespace MageOS\ShoppingFeed\Test\Unit\Model\Feed\Source\Shipping;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class AvailableMethodsTest extends ModelFramework
{
    /** @var \MageOS\ShoppingFeed\Model\Feed\Source\Shipping\AvailableMethods */
    protected $sourceAvailableMethods;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingMethodConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\OfflineShipping\Model\Carrier\Flatrate
     */
    protected $carrierFlatrate;

    /**
     * @var \Magento\OfflineShipping\Model\Carrier\Tablerate
     */
    protected $carrierTablerate;

    /**
     * @var \Magento\OfflineShipping\Model\Carrier\Freeshipping
     */
    protected $carrierFreeshiping;

    /**
     * @var \Magento\Fedex\Model\Carrier
     */
    protected $carrierFedex;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        /** @var \MageOS\ShoppingFeed\Model\Feed $feed */
        $feed = $this->getModelMock('\MageOS\ShoppingFeed\Model\Feed');

        /** @var \Magento\Framework\Registry $registry */
        $registry = $this->getModelMock('\Magento\Framework\Registry');

        /** @var \Magento\Shipping\Model\Config $this->shippingMethodConfig */
        $this->shippingMethodConfig = $this->getModelMock('\Magento\Shipping\Model\Config');

        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $this->scopeConfig */
        $this->scopeConfig = $this->getModelMock('\Magento\Framework\App\Config\ScopeConfigInterface');

        /** @var \Magento\OfflineShipping\Model\Carrier\Flatrate $this->carrierFlatrate */
        $this->carrierFlatrate = $this->getModelMock('\Magento\OfflineShipping\Model\Carrier\Flatrate');

        /** @var \Magento\OfflineShipping\Model\Carrier\Tablerate $this->carrierTablerate */
        $this->carrierTablerate = $this->getModelMock('\Magento\OfflineShipping\Model\Carrier\Tablerate');

        /** @var \Magento\OfflineShipping\Model\Carrier\Freeshipping $this->carrierFreeshiping */
        $this->carrierFreeshiping = $this->getModelMock('\Magento\OfflineShipping\Model\Carrier\Freeshipping');

        /** @var \Magento\Fedex\Model\Carrier $this->carrierFedex */
        $this->carrierFedex = $this->getModelMock('\Magento\Fedex\Model\Carrier');

        $feed->expects($this->exactly(1))
            ->method('getConfig')
            ->with('shipping_carrier_realtime', [])
            ->will($this->returnValue(['ups','usps','fedex','dhl','dhlint']));

        $registry->expects($this->once())
            ->method('registry')
            ->with('feed')
            ->will($this->returnValue($feed));

        $this->shippingMethodConfig->expects($this->once())
            ->method('getActiveCarriers')
            ->will($this->returnValue([$this->carrierFlatrate, $this->carrierTablerate, $this->carrierFreeshiping, $this->carrierFedex]));

        $this->sourceAvailableMethods = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Feed\Source\Shipping\AvailableMethods',
            [
                'registry' => $registry,
                'shippingMethodConfig' => $this->shippingMethodConfig,
                'scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    protected function getExpectedCarrierFlatrate()
    {
        return [
            'label' => 'Flat Rate', 'value' => [
                0 => ['value' => 'flatrate_flatrate', 'label' => '[flatrate] Fixed'],
            ],
        ];
    }

    protected function getExpectedCarrierTablerate()
    {
        return [
            'label' => 'Best Way', 'value' => [
                0 => ['value' => 'tablerate_bestway', 'label' => '[tablerate] Table Rate'],
            ],
        ];
    }

    protected function getExpectedCarrierFreeshipping()
    {
        return [
            'label' => 'Free Shipping', 'value' => [
                0 => ['value' => 'freeshipping_freeshipping', 'label' => '[freeshipping] Free'],
            ],
        ];
    }

    protected function prepareFlatrateMock($isActive = true, $hasMethods = true)
    {
        $this->carrierFlatrate->expects($this->once())
            ->method('getCarrierCode')
            ->will($this->returnValue('flatrate'));
        $this->carrierFlatrate->expects($this->once())
            ->method('isActive')
            ->will($this->returnValue($isActive));

        if ($isActive && $hasMethods) {
            $this->carrierFlatrate->expects($this->once())
                ->method('getAllowedMethods')
                ->will($this->returnValue(['flatrate' => 'Fixed']));
        }
    }

    protected function prepareTablerateMock($isActive = true, $hasMethods = true)
    {
        $this->carrierTablerate->expects($this->once())
            ->method('getCarrierCode')
            ->will($this->returnValue('tablerate'));
        $this->carrierTablerate->expects($this->once())
            ->method('isActive')
            ->will($this->returnValue($isActive));

        if ($isActive && $hasMethods) {
            $this->carrierTablerate->expects($this->once())
                ->method('getAllowedMethods')
                ->will($this->returnValue(['bestway' => 'Table Rate']));
        }
    }

    protected function prepareFreeshippingMock($isActive = true, $hasMethods = true)
    {
        $this->carrierFreeshiping->expects($this->once())
            ->method('getCarrierCode')
            ->will($this->returnValue('freeshipping'));
        $this->carrierFreeshiping->expects($this->once())
            ->method('isActive')
            ->will($this->returnValue($isActive));

        if ($isActive && $hasMethods) {
            $this->carrierFreeshiping->expects($this->once())
                ->method('getAllowedMethods')
                ->will($this->returnValue(['freeshipping' => 'Free']));
        }
    }

    protected function prepareFedexMock($isActive = true, $hasMethods = true)
    {
        $this->carrierFedex->expects($this->once())
            ->method('getCarrierCode')
            ->will($this->returnValue('fedex'));
    }

    private function expectCarrierTitles(array $titles): void
    {
        $paths = array_keys($titles);
        $values = array_values($titles);
        $call = 0;

        $this->scopeConfig->expects($this->exactly(count($titles)))
            ->method('getValue')
            ->willReturnCallback(function ($path, $scope, $scopeId) use (&$call, $paths, $values) {
                $this->assertSame([$paths[$call], 'store', null], [$path, $scope, $scopeId]);

                return $values[$call++];
            });
    }

    public function testToOptionArray()
    {
        $this->prepareFlatrateMock(true);
        $this->prepareTablerateMock(true);
        $this->prepareFreeshippingMock(true);
        $this->prepareFedexMock(true);

        $this->expectCarrierTitles([
            'carriers/flatrate/title' => 'Flat Rate',
            'carriers/tablerate/title' => 'Best Way',
            'carriers/freeshipping/title' => 'Free Shipping',
        ]);

        $this->assertEquals([
            ['value' => '', 'label' => ''],
            'flatrate' => $this->getExpectedCarrierFlatrate(),
            'tablerate' => $this->getExpectedCarrierTablerate(),
            'freeshipping' => $this->getExpectedCarrierFreeshipping(),
        ], $this->sourceAvailableMethods->toOptionArray());
    }

    public function testToOptionArrayCarrierNotActive()
    {
        $this->prepareFlatrateMock(true);
        $this->prepareTablerateMock(false);
        $this->prepareFreeshippingMock(true);
        $this->prepareFedexMock(true);

        $this->expectCarrierTitles([
            'carriers/flatrate/title' => 'Flat Rate',
            'carriers/freeshipping/title' => 'Free Shipping',
        ]);

        $this->assertEquals([
            ['value' => '', 'label' => ''],
            'flatrate' => $this->getExpectedCarrierFlatrate(),
            'freeshipping' => $this->getExpectedCarrierFreeshipping(),
        ], $this->sourceAvailableMethods->toOptionArray());
    }

    public function testToOptionArrayCarrierWithoutMethods()
    {
        $this->prepareFlatrateMock(true);
        $this->prepareTablerateMock(true, false);
        $this->prepareFreeshippingMock(true);
        $this->prepareFedexMock(true);

        $this->expectCarrierTitles([
            'carriers/flatrate/title' => 'Flat Rate',
            'carriers/freeshipping/title' => 'Free Shipping',
        ]);

        $this->assertEquals([
            ['value' => '', 'label' => ''],
            'flatrate' => $this->getExpectedCarrierFlatrate(),
            'freeshipping' => $this->getExpectedCarrierFreeshipping(),
        ], $this->sourceAvailableMethods->toOptionArray());
    }

    public function testToOptionArrayWithOptionsAlreadySet()
    {
        $this->prepareFlatrateMock(true);
        $this->prepareTablerateMock(true);
        $this->prepareFreeshippingMock(true);
        $this->prepareFedexMock(true);

        $this->expectCarrierTitles([
            'carriers/flatrate/title' => 'Flat Rate',
            'carriers/tablerate/title' => 'Best Way',
            'carriers/freeshipping/title' => 'Free Shipping',
        ]);

        $this->sourceAvailableMethods->toOptionArray();
        $this->assertEquals([
            ['value' => '', 'label' => ''],
            'flatrate' => $this->getExpectedCarrierFlatrate(),
            'tablerate' => $this->getExpectedCarrierTablerate(),
            'freeshipping' => $this->getExpectedCarrierFreeshipping(),
        ], $this->sourceAvailableMethods->toOptionArray());
    }
}
