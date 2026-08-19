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

namespace MageOS\ShoppingFeed\Test\Unit\Model\Feed\Source\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ColumnsTest extends ModelFramework
{
    /** @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns */
    protected $sourceColumns;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        /** @var \Magento\Framework\DataObject $feedConfigMock */
        $feedConfigMock = $this->getModelMock('\Magento\Framework\DataObject');

        /** @var \MageOS\ShoppingFeed\Model\Feed $feedMock */
        $feedMock = $this->getModelMock('\MageOS\ShoppingFeed\Model\Feed');

        /** @var \Magento\Framework\Registry $registryMock */
        $registryMock = $this->getModelMock('\Magento\Framework\Registry');

        $feedConfigMock->expects($this->exactly(1))
            ->method('getData')
            ->with('columns_product_columns')
            ->will($this->returnValue([
                ['column' => 'id', 'order' => '10', 'param' => '0'],
                ['column' => 'title', 'order' => '20'],
                ['column' => 'description', 'order' => '30']
            ]));

        $feedMock->expects($this->exactly(2))
            ->method('getConfig')
            ->will($this->returnValue($feedConfigMock));

        $registryMock->expects($this->once())
            ->method('registry')
            ->with('feed')
            ->will($this->returnValue($feedMock));

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->sourceColumns = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns',
            [
                'coreRegistry' => $registryMock,
            ]
        );
    }

    public function testGetToOptionArray()
    {
        $this->assertEquals([
            ['value' => null, 'label' => ''],
            ['value' => 'id', 'label' => 'id'],
            ['value' => 'title', 'label' => 'title'],
            ['value' => 'description', 'label' => 'description'],
        ], $this->sourceColumns->toOptionArray());
    }
}
