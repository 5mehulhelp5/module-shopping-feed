<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Ui\Component\Listing;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class MassAction extends \Magento\Ui\Component\MassAction
{
    public function __construct(
        ContextInterface $context,
        private AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
    }

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
