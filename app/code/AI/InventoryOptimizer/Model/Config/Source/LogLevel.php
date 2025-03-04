<?php
namespace AI\InventoryOptimizer\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class LogLevel implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'emergency', 'label' => __('Emergency')],
            ['value' => 'alert', 'label' => __('Alert')],
            ['value' => 'critical', 'label' => __('Critical')],
            ['value' => 'error', 'label' => __('Error')],
            ['value' => 'warning', 'label' => __('Warning')],
            ['value' => 'notice', 'label' => __('Notice')],
            ['value' => 'info', 'label' => __('Info')],
            ['value' => 'debug', 'label' => __('Debug')]
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
            'emergency' => __('Emergency'),
            'alert' => __('Alert'),
            'critical' => __('Critical'),
            'error' => __('Error'),
            'warning' => __('Warning'),
            'notice' => __('Notice'),
            'info' => __('Info'),
            'debug' => __('Debug')
        ];
    }
} 