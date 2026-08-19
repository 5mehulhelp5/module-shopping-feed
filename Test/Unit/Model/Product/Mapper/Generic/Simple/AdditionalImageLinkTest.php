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


namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Generic\Simple;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

/**
 * Class AdditionalImageLink
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class AdditionalImageLinkTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\AdditionalImageLink
     */
    protected $model;


    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\AdditionalImageLink',
            []
        );
    }

    public function testMap()
    {
        $this->expectReturn($this->productMock, 'getId', 1);
        $this->expectAdvencedReturn(
            $this->productMock,
            'getData',
            $this->returnCallback(function ($arg) {
                switch ($arg) {
                    case 'image':
                        return 'test_image.jpg';
                }
                return '';
            })
        );

            $this->expectReturn(
                $this->productMock,
                'getMediaGalleryImages',
                [
                ['file' => 'media_image.png', 'disabled' => true],
                ['file' => 'test_image.jpg', 'disabled' => false],
                ['file' => 'media_image.png', 'disabled' => false],
                ['file' => 'media_image.png', 'disabled' => false],
                ['file' => 'media_image.png', 'disabled' => false],
                ['file' => 'media_image.png', 'disabled' => false],
                ['file' => 'media_image.png', 'disabled' => false],
                ['file' => 'media_image.png', 'disabled' => false],
                ['file' => 'media_image.png', 'disabled' => false],
                ['file' => 'media_image.png', 'disabled' => false],
                ['file' => 'media_image.png', 'disabled' => false]

                ]
            );
        $this->expectReturn($this->adapterMock, 'getProduct', $this->productMock);
        $this->expectReturn($this->adapterMock, 'getData', 'url_prefix');
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
        $this->expectAdvencedReturn(
            $this->feedMock,
            'getConfig',
            $this->returnCallback(static function ($key, $default = '') {
                return $default;
            })
        );

        $this->model->addAdapter($this->adapterMock);

        $params = ['column' => 'test'];
        $cell = $this->model->map($params);
        $expected = 'url_prefix/media_image.png,url_prefix/media_image.png,url_prefix/media_image.png,url_prefix/media_image.png,url_prefix/media_image.png,url_prefix/media_image.png,url_prefix/media_image.png,url_prefix/media_image.png';
        $this->assertEquals($expected, $cell);
    }
}
