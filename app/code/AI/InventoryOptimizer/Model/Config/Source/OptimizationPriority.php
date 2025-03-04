<?php
namespace AI\InventoryOptimizer\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class OptimizationPriority implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'cost', 'label' => __('Cost (Cheapest Shipping)')],
            ['value' => 'speed', 'label' => __('Speed (Fastest Delivery)')],
            ['value' => 'balanced', 'label' => __('Balanced (Cost & Speed)')],
            ['value' => 'inventory', 'label' => __('Inventory Balancing')]
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
            'cost' => __('Cost (Cheapest Shipping)'),
            'speed' => __('Speed (Fastest Delivery)'),
            'balanced' => __('Balanced (Cost & Speed)'),
            'inventory' => __('Inventory Balancing')
        ];
    }
} 