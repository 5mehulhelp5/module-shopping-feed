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

namespace MageOS\ShoppingFeed\Test\Unit\Model\FeedTypes;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;
use PHPUnit\Framework\Attributes\DataProvider;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ConfigTest extends ModelFramework
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \MageOS\ShoppingFeed\Model\FeedTypes\Config
     */
    protected $model;

    protected $serializerMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \MageOS\ShoppingFeed\Model\Serializer
     */
    protected $serializer;

    protected function setUp(): void
    {
        $this->serializer = new \Magento\Framework\Serialize\Serializer\Json();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->readerMock = $this->getModelMock('MageOS\ShoppingFeed\Model\FeedTypes\Config\Reader');
        $this->cacheMock = $this->getModelMock('Magento\Framework\Config\CacheInterface');
        $this->serializerMock = $this->getModelMock('Magento\Framework\Serialize\SerializerInterface');
    }

    /**
     * @dataProvider getFeedDataProvider
     *
     * @param array $value
     * @param mixed $expected
     */
    #[DataProvider('getFeedDataProvider')]
    public function testGetFeed($value, $expected)
    {
        $this->cacheMock->expects($this->any())->method('load')->will(
            $this->returnValue(
                $this->serializer->serialize($value)
            )
        );
        $this->serializerMock->expects($this->any())->method('unserialize')->will($this->returnValue($value));

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\FeedTypes\Config',
            [
                'reader' => $this->readerMock,
                'cache' => $this->cacheMock,
                'cacheId' => 'cache_id',
                'serializer' => $this->serializerMock
            ]
        );

        $this->assertEquals($expected, $this->model->getFeed('google_shoping'));
    }

    public static function getFeedDataProvider()
    {
        return [
            'global_key_exist' => [['feed' => ['google_shoping' => 'value']], 'value'],
            'return_default_value' => [['feed' => ['some_key' => 'value']], []]
        ];
    }

    public function testGetAll()
    {
        $expected = ['Expected Data'];
        $this->cacheMock->expects($this->once())->method('load')->will(
            $this->returnValue(
                $this->serializer->serialize(['feed' => $expected])
            )
        );
        $this->serializerMock->expects($this->any())->method('unserialize')->will($this->returnValue(['feed' => $expected]));

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\FeedTypes\Config',
            [
                'reader' => $this->readerMock,
                'cache' => $this->cacheMock,
                'cacheId' => 'cache_id',
                'serializer' => $this->serializerMock
            ]
        );

        $result = $this->model->getAll();
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getIsAllowedDirectiveProvider
     *
     * @param array $value
     * @param mixed $expected
     */
    #[DataProvider('getIsAllowedDirectiveProvider')]
    public function testIsAllowedDirective($value, $expected)
    {
        $this->cacheMock->expects($this->once())->method('load')->will(
            $this->returnValue(
                $this->serializer->serialize($value)
            )
        );
        $this->serializerMock->expects($this->once())->method('unserialize')->will($this->returnValue($value));

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\FeedTypes\Config',
            [
                'reader' => $this->readerMock,
                'cache' => $this->cacheMock,
                'cacheId' => 'cache_id',
                'serializer' => $this->serializerMock
            ]
        );

        $this->assertEquals($expected, $this->model->isAllowedDirective('generic', 'some_directive'));
    }

    public static function getIsAllowedDirectiveProvider()
    {
        return [
            'is_allowed' => [['feed' => ['generic' => ['directives' => ['some_directive' => ['Expected Data']]]]], true],
            'is_not_allowed' => [['feed' => ['generic' => ['directives' => ['some_other_directive' => ['Expected Data']]]]], false],
        ];
    }

    /**
     * @dataProvider getDirectiveProvider
     *
     * @param $value
     * @param $expected
     */
    #[DataProvider('getDirectiveProvider')]
    public function testGetDirective($value, $expected)
    {
        $this->cacheMock->expects($this->once())->method('load')->will(
            $this->returnValue(
                $this->serializer->serialize($value)
            )
        );
        $this->serializerMock->expects($this->once())->method('unserialize')->will($this->returnValue($value));

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\FeedTypes\Config',
            [
                'reader' => $this->readerMock,
                'cache' => $this->cacheMock,
                'cacheId' => 'cache_id',
                'serializer' => $this->serializerMock
            ]
        );

        $this->assertEquals($expected, $this->model->getDirective('generic', 'some_directive'));
    }

    public static function getDirectiveProvider()
    {
        return [
            'is_allowed' => [['feed' => ['generic' => ['directives' => ['some_directive' => ['Expected Data']]]]], ['Expected Data']]
        ];
    }
}
