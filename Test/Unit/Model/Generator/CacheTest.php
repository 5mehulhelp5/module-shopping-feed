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


namespace MageOS\ShoppingFeed\Test\Unit\Model\Generator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class CacheTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CacheTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Cache
     */
    protected $model;

    protected $jsonEncoderMock;

    protected $jsonDecoderMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->jsonEncoderMock = $this->getModelMock(
            'Magento\Framework\Json\Encoder',
            ['encode']
        );
        $this->jsonDecoderMock = $this->getModelMock(
            'Magento\Framework\Json\Decoder',
            ['decode']
        );
        $this->cacheMock = $this->getModelMock(
            'Magento\Framework\App\Cache',
            ['load', 'remove', 'save']
        );
        $this->expectSelf($this->cacheMock, ['save', 'remove']);
        $this->expectAdvencedReturn($this->jsonEncoderMock, 'encode', $this->returnArgument(0));
        $this->expectAdvencedReturn($this->jsonDecoderMock, 'decode', $this->returnArgument(0));

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Generator\Cache',
            [
                'jsonEncoder' => $this->jsonEncoderMock,
                'jsonDecoder' => $this->jsonDecoderMock,
                'cacheInterface' => $this->cacheMock
            ]
        );
    }

    public function testCache()
    {
        $path = 'some/custom/test/path';
        $value = 'Custom value';

        $this->model->setCache($path, $value);

        $expected = $value;
        $this->assertEquals($expected, $this->model->getCache($path));
    }

    public function testDefaultCacheValue()
    {
        $expected = 'Default value';
        $this->assertEquals($expected, $this->model->getCache('non/existing/cache/path', 'Default value'));
    }
}
