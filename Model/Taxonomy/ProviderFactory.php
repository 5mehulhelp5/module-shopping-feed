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

namespace MageOS\ShoppingFeed\Model\Taxonomy;

/**
 * Factory class for \MageOS\ShoppingFeed\Model\Taxonomy\Type\*
 */
class ProviderFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * @var \MageOS\ShoppingFeed\Model\FeedTypes\Config
     */
    protected $feedTypesConfig;

    /**
     * Created instances, keyed by feed type
     *
     * @var array
     */
    protected $_instances = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface       $objectManager
     * @param \MageOS\ShoppingFeed\Model\FeedTypes\Config $feedTypesConfig
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \MageOS\ShoppingFeed\Model\FeedTypes\Config $feedTypesConfig
    ) {
        $this->_objectManager = $objectManager;
        $this->feedTypesConfig = $feedTypesConfig;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param  \MageOS\ShoppingFeed\Model\Feed $feed
     * @return \MageOS\ShoppingFeed\Model\Taxonomy\ProviderInterface
     */
    public function create(\MageOS\ShoppingFeed\Model\Feed $feed, $singleton = true)
    {
        try {
            $feedType = $feed->getType();

            if (!isset($this->_instances[$feedType]) || !$singleton) {
                $className = $this->feedTypesConfig->getTaxonomyProvider($feedType);

                if (!class_exists($className)) {
                    $className = 'MageOS\ShoppingFeed\Model\Taxonomy\Type\Generic';
                }

                $object = $this->_objectManager->create(
                    $className, [
                    'feed'    => $feed
                    ]
                );
                if ($singleton) {
                    $this->_instances[$feedType] = $object;
                } else {
                    return $object;
                }
            } else {
                $this->_instances[$feedType]
                    ->unsetData()
                    ->setFeed($feed);
            }

            return $this->_instances[$feedType];
        } catch (\Exception $e) {
            return false;
        }
    }
}
