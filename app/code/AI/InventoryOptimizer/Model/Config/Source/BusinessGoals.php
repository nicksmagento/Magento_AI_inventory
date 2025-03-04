<?php
namespace AI\InventoryOptimizer\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class BusinessGoals implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'revenue_growth', 'label' => __('Revenue Growth')],
            ['value' => 'customer_satisfaction', 'label' => __('Customer Satisfaction')],
            ['value' => 'operational_efficiency', 'label' => __('Operational Efficiency')],
            ['value' => 'inventory_optimization', 'label' => __('Inventory Optimization')],
            ['value' => 'loss_prevention', 'label' => __('Loss Prevention')],
            ['value' => 'market_expansion', 'label' => __('Market Expansion')],
            ['value' => 'cost_reduction', 'label' => __('Cost Reduction')],
            ['value' => 'sustainability', 'label' => __('Sustainability')]
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
            'revenue_growth' => __('Revenue Growth'),
            'customer_satisfaction' => __('Customer Satisfaction'),
            'operational_efficiency' => __('Operational Efficiency'),
            'inventory_optimization' => __('Inventory Optimization'),
            'loss_prevention' => __('Loss Prevention'),
            'market_expansion' => __('Market Expansion'),
            'cost_reduction' => __('Cost Reduction'),
            'sustainability' => __('Sustainability')
        ];
    }
} 