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


namespace MageOS\ShoppingFeed\Model\FeedTypes\Config;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = [
        '/config/feed' => 'name',
        '/config/feed/directives/directive' => 'name',
        '/config/feed/default_product_columns/column' => 'attribute'
    ];

    /**
     * @param \Magento\Framework\Config\FileResolverInterface               $fileResolver
     * @param \MageOS\ShoppingFeed\Model\FeedTypes\Config\Converter     $converter
     * @param \MageOS\ShoppingFeed\Model\FeedTypes\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface            $validationState
     * @param string                                                        $fileName
     * @param array                                                         $idAttributes
     * @param string                                                        $domDocumentClass
     * @param string                                                        $defaultScope
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \MageOS\ShoppingFeed\Model\FeedTypes\Config\Converter $converter,
        \MageOS\ShoppingFeed\Model\FeedTypes\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'mageos_shopping_feed.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
}
