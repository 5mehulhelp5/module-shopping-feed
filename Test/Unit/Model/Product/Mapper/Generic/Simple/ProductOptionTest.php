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
 * Class ProductOptionTest
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ProductOptionTest extends ModelFramework
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Id
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->expectSelf($this->adapterMock, 'getOptionProcessor');

        $this->model = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\ProductOption',
            []
        );
        $this->model->addAdapter($this->adapterMock);
    }

    public function testMap()
    {
        $params = ['param' => 'option', 'column' => 'name'];

        $option = $this->getModelMock('Magento\Catalog\Model\Product\Option', ['getTitle']);
        $this->expectAdvencedReturn(
            $option,
            'getTitle',
            $this->onConsecutiveCalls('option1', 'option2')
        );

        $optionCollection = [$option, $option];
        $this->expectReturn($this->adapterMock, 'getOptions', [$optionCollection]);

        $this->assertEquals('option1,option2', $this->model->map($params));
    }
}
