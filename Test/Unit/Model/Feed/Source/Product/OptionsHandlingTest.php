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
class OptionsHandlingTest extends ModelFramework
{
    /** @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\OptionHandling */
    protected $sourceOptionHandling;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\OptionHandling */
    protected $sourceMode;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->sourceMode = $this->objectManagerHelper->getObject(
            'MageOS\ShoppingFeed\Model\Feed\Source\Product\OptionHandling'
        );
    }

    public function testGetOptionArray()
    {
        $this->assertEquals([
            0 => 'One row, having options concatenated in the column output',
            1 => 'Multiple rows, one for each option',
        ], $this->sourceMode->getOptionArray());
    }

    public function testGetToOptionArray()
    {
        $this->assertEquals([
            ['value' => 0, 'label' => 'One row, having options concatenated in the column output'],
            ['value' => 1, 'label' => 'Multiple rows, one for each option'],
        ], $this->sourceMode->toOptionArray());
    }
}
