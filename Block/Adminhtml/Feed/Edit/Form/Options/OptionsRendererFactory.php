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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Options;

/**
 * Factory class for \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Options\Renderer\*
 */
class OptionsRendererFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Created instances, keyed by renderer class name
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param  $renderer
     * @return \Magento\Framework\View\Element\Template
     */
    public function create($renderer, $singleton = true)
    {
        if (!isset($this->instances[$renderer]) || !$singleton) {
            $object = $this->objectManager->create($renderer);
            if ($singleton) {
                $this->instances[$renderer] = $object;
            } else {
                return $object;
            }
        }

        return $this->instances[$renderer];
    }
}
