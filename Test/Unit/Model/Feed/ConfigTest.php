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


namespace MageOS\ShoppingFeed\Test\Unit\Model\Feed;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class ConfigTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ConfigTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Config
     */
    protected $model;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var \Magento\Framework\Json\DecoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonDecoderMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    public function setUp(): void
    {
        $eventManager = $this->createMock('Magento\Framework\Event\ManagerInterface');
        $eventManager->expects($this->any())
            ->method('dispatch')
            ->will($this->returnSelf());

        $contextMock = $this->createMock('Magento\Framework\Model\Context');
        $contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->will($this->returnValue($eventManager));
        $registryMock = $this->createMock('Magento\Framework\Registry');
        $resource = $this->createMock('Magento\Review\Model\ResourceModel\Review');
        $resourceCollection = $this->createMock('Magento\Framework\Data\Collection\AbstractDb');

        $this->jsonEncoderMock = $this->getModelMock(
            'Magento\Framework\Json\EncoderInterface',
            ['encode']
        );

        $this->jsonDecoderMock = $this->getModelMock(
            'Magento\Framework\Json\DecoderInterface',
            ['decode']
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Feed\Config',
            [
                'context' => $contextMock,
                'registry' => $registryMock,
                'jsonEncoder' => $this->jsonEncoderMock,
                'jsonDecoder' => $this->jsonDecoderMock,
                'resource' => $resource,
                'resourceCollection' => $resourceCollection
            ]
        );
    }


    public function testBeforeSave()
    {
        $this->model->setData('value', ['test' => 'value']);

        $this->jsonEncoderMock->expects($this->any())
            ->method('encode')
            ->will($this->returnValue('new_value'));

        $expected = 'new_value';
        $this->model->beforeSave();
        $this->assertEquals($expected, $this->model->getData('value'));
    }

    public function testAfterLoad()
    {
        $this->model->setData('value', '["ups","usps"]');

        $this->jsonDecoderMock->expects($this->any())
            ->method('decode')
            ->will($this->returnValue(['ups', 'usps']));

        $expected = ['ups', 'usps'];
        $this->model->afterLoad();
        $this->assertEquals($expected, $this->model->getData('value'));
    }
    public function testScalarAndStructuredSettingsRoundTripWithoutChangingType(): void
    {
        $this->jsonEncoderMock->method('encode')->willReturnCallback(static fn($value) => json_encode($value));
        $this->jsonDecoderMock->method('decode')->willReturnCallback(static fn($value) => json_decode($value, true, 512, JSON_THROW_ON_ERROR));
        foreach (['[plain text default]', '[1,2]', '{"key":"value"}', '"quoted"', '0', '', '__mageos_shopping_feed_string__:literal', ['a' => 'b'], []] as $value) {
            $this->model->setData('value', $value);
            $this->model->beforeSave();
            $stored = $this->model->getData('value');
            $this->model->setData('value', $stored);
            $this->model->afterLoad();
            $this->assertSame($value, $this->model->getData('value'));
        }
    }

    public function testLegacyMalformedJsonRemainsPlainText(): void
    {
        $this->jsonDecoderMock->method('decode')->willReturnCallback(static function ($value) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON');
            }
            return $decoded;
        });
        foreach (['[plain text default]', '{template}', '"quoted"', 'ordinary text', '["ups","usps"]'] as $value) {
            $this->model->setData('value', $value)->afterLoad();
            $expected = $value === '["ups","usps"]' ? ['ups', 'usps'] : $value;
            $this->assertSame($expected, $this->model->getData('value'));
        }
    }

    public function testDecoderReturningNullDoesNotEraseLegacyText(): void
    {
        $this->jsonDecoderMock->method('decode')->willReturn(null);
        $this->model->setData('value', '[plain text default]')->afterLoad();
        $this->assertSame('[plain text default]', $this->model->getData('value'));
    }

    public function testOrdinaryTextKeepsItsLegacyStorageFormat(): void
    {
        $this->jsonEncoderMock->method('encode')->willReturnCallback(static fn($value) => json_encode($value));
        foreach (['normal text', '0', '', '"quoted"'] as $value) {
            $this->model->setData('value', $value)->beforeSave();
            $this->assertSame($value, $this->model->getData('value'));
        }
    }

}
