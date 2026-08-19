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

namespace MageOS\ShoppingFeed\Test\Unit\Model\Feed\Source\Schedule;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class StartAtTest extends ModelFramework
{
    /** @var \MageOS\ShoppingFeed\Model\Feed\Source\Schedule\StartAt */
    protected $startAt;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        /** @var \DateTime $dateTimeMock */
        $datetimeMock = $this->createMock('\DateTime');

        /** @var \Magento\Framework\Locale\ResolverInterface $localeResolverMock */
        $localeResolverMock = $this->createMock('\Magento\Framework\Locale\ResolverInterface');

        $localeResolverMock->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue('en_US'));

        /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDateMock */
        $localeDateMock = $this->getModelMock(
            '\Magento\Framework\Stdlib\DateTime\TimezoneInterface'
        );

        $localeDateMock->expects($this->exactly(24))
            ->method('date')
            ->will($this->returnValue($datetimeMock));

        $datetimeMock->expects($this->exactly(24))
            ->method('setTime')
            ->will($this->returnValue($datetimeMock));

        $localeDateMock->expects($this->exactly(24))
            ->method('formatDateTime')
            ->will($this->onConsecutiveCalls(
                '12:00 AM',
                '1:00 AM',
                '2:00 AM',
                '3:00 AM',
                '4:00 AM',
                '5:00 AM',
                '6:00 AM',
                '7:00 AM',
                '8:00 AM',
                '9:00 AM',
                '10:00 AM',
                '11:00 AM',
                '12:00 PM',
                '1:00 PM',
                '2:00 PM',
                '3:00 PM',
                '4:00 PM',
                '5:00 PM',
                '6:00 PM',
                '7:00 PM',
                '8:00 PM',
                '9:00 PM',
                '10:00 PM',
                '11:00 PM'
            ));

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->startAt = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Feed\Source\Schedule\StartAt',
            [
                'localeResolver' => $localeResolverMock,
                'localeDate' => $localeDateMock,
            ]
        );
    }

    public function testToOptionArray()
    {
        $this->assertEquals([
            ['value' => 0, 'label' => '12:00 AM'],
            ['value' => 1, 'label' => '1:00 AM'],
            ['value' => 2, 'label' => '2:00 AM'],
            ['value' => 3, 'label' => '3:00 AM'],
            ['value' => 4, 'label' => '4:00 AM'],
            ['value' => 5, 'label' => '5:00 AM'],
            ['value' => 6, 'label' => '6:00 AM'],
            ['value' => 7, 'label' => '7:00 AM'],
            ['value' => 8, 'label' => '8:00 AM'],
            ['value' => 9, 'label' => '9:00 AM'],
            ['value' => 10, 'label' => '10:00 AM'],
            ['value' => 11, 'label' => '11:00 AM'],
            ['value' => 12, 'label' => '12:00 PM'],
            ['value' => 13, 'label' => '1:00 PM'],
            ['value' => 14, 'label' => '2:00 PM'],
            ['value' => 15, 'label' => '3:00 PM'],
            ['value' => 16, 'label' => '4:00 PM'],
            ['value' => 17, 'label' => '5:00 PM'],
            ['value' => 18, 'label' => '6:00 PM'],
            ['value' => 19, 'label' => '7:00 PM'],
            ['value' => 20, 'label' => '8:00 PM'],
            ['value' => 21, 'label' => '9:00 PM'],
            ['value' => 22, 'label' => '10:00 PM'],
            ['value' => 23, 'label' => '11:00 PM'],
        ], $this->startAt->toOptionArray());
    }
}
