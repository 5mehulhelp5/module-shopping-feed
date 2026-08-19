<?php
namespace MageOS\ShoppingFeed\Model\Logger\Source;

class LogLevel implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Monolog\Logger::DEBUG, 'label' => __('Debug')],
            ['value' => \Monolog\Logger::INFO, 'label' => __('Info')],
            ['value' => \Monolog\Logger::NOTICE, 'label' => __('Notice')],
            ['value' => \Monolog\Logger::WARNING, 'label' => __('Warning')],
            ['value' => \Monolog\Logger::ERROR, 'label' => __('Error')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            \Monolog\Logger::DEBUG => __('Debug'),
            \Monolog\Logger::INFO => __('Info'),
            \Monolog\Logger::NOTICE => __('Notice'),
            \Monolog\Logger::WARNING => __('Warning'),
            \Monolog\Logger::ERROR => __('Error')
        ];
    }
}