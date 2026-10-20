<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Ui\Component\Listing;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class MassAction extends \Magento\Ui\Component\MassAction
{
    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param AuthorizationInterface $authorization
     * @param \Magento\Framework\View\Element\UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        private AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
    }

    /**
     * Prepare the mass actions, keeping only those the current admin user is allowed to run.
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $config = $this->getConfiguration();
        $config['actions'] = array_values(array_filter(
            $config['actions'] ?? [],
            fn (array $action): bool => !isset($action['aclResource'])
                || $this->authorization->isAllowed($action['aclResource'])
        ));
        if (!$config['actions']) {
            $config['componentDisabled'] = true;
        }
        $this->setData('config', $config);
    }
}
